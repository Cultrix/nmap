<?php

/**
 * This file is part of the nmap package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Nmap;

use Nmap\Util\ProcessExecutor;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Aitor García <aitor.falc@gmail.com>
 */
class Nmap
{
    private $executor;

    private $outputFile;

    private $enableOsDetection = false;

    private $enableServiceInfo = false;

    private $enableVerbose     = false;

    private $disablePortScan   = false;

    private $disableReverseDNS = false;

    private $treatHostsAsOnline = false;

    private $executable;

    private $timeout = 60;

    /**
     * @return Nmap
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param ProcessExecutor $executor
     * @param string          $outputFile
     * @param string          $executable
     * @param int             $timeout
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ProcessExecutor $executor = null, $outputFile = null, $executable = 'nmap')
    {
        $this->executor   = $executor ?: new ProcessExecutor();
        $this->outputFile = $outputFile ?: tempnam(sys_get_temp_dir(), 'nmap-scan-output.xml');
        $this->executable = $executable;
    }

    /**
     * @param array $targets
     * @param array $ports
     *
     * @return Host[]
     */
    public function scan(array $targets, array $ports = array())
    {
        $command = [$this->executable];

        if (true === $this->enableOsDetection) {
            $command[] = '-O';
        }

        if (true === $this->enableServiceInfo) {
            $command[] = '-sV';
        }

        if (true === $this->enableVerbose) {
            $command[] = '-v';
        }

        if (true === $this->disablePortScan) {
            $command[] = '-sn';
        } elseif (!empty($ports)) {
            $command[] = '-p '.implode(',', $ports);
        }

        if (true === $this->disableReverseDNS) {
            $command[] = '-n';
        }

        if (true == $this->treatHostsAsOnline) {
            $command[] = '-Pn';
        }
        
        $command = array_merge($command,$targets);

        $command[] = '-oX';
        $command[] = $this->outputFile;

        $this->executor->execute($command, $this->timeout);

        if (!file_exists($this->outputFile)) {
            throw new \RuntimeException(sprintf('Output file not found ("%s")', $this->outputFile));
        }

        return $this->parseOutputFile($this->outputFile);
    }

    /**
     * @param boolean $enable
     *
     * @return Nmap
     */
    public function enableOsDetection($enable = true)
    {
        $this->enableOsDetection = $enable;

        return $this;
    }

    /**
     * @param boolean $enable
     *
     * @return Nmap
     */
    public function enableServiceInfo($enable = true)
    {
        $this->enableServiceInfo = $enable;

        return $this;
    }

    /**
     * @param boolean $enable
     *
     * @return Nmap
     */
    public function enableVerbose($enable = true)
    {
        $this->enableVerbose = $enable;

        return $this;
    }

    /**
     * @param boolean $disable
     *
     * @return Nmap
     */
    public function disablePortScan($disable = true)
    {
        $this->disablePortScan = $disable;

        return $this;
    }

    /**
     * @param boolean $disable
     *
     * @return Nmap
     */
    public function disableReverseDNS($disable = true)
    {
        $this->disableReverseDNS = $disable;

        return $this;
    }

    /**
     * @param boolean $disable
     *
     * @return Nmap
     */
    public function treatHostsAsOnline($disable = true)
    {
        $this->treatHostsAsOnline = $disable;

        return $this;
    }

    /**
     * @param $timeout
     *
     * @return Nmap
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    private function parseOutputFile($xmlFile)
    {
        $xml = simplexml_load_file($xmlFile);

        $hosts = array();
        foreach ($xml->host as $host) {
            $hosts[] = new Host(
                $this->parseAddresses($host),
                (string) $host->status->attributes()->state,
                isset($host->hostnames) ? $this->parseHostnames($host->hostnames->hostname) : array(),
                isset($host->ports) ? $this->parsePorts($host->ports->port) : array()
            );
        }

        return $hosts;
    }

    private function parseHostnames(\SimpleXMLElement $xmlHostnames)
    {
        $hostnames = array();
        foreach ($xmlHostnames as $hostname) {
            $hostnames[] = new Hostname(
                (string) $hostname->attributes()->name,
                (string) $hostname->attributes()->type
            );
        }

        return $hostnames;
    }

    private function parsePorts(\SimpleXMLElement $xmlPorts)
    {
        $ports = array();
        foreach ($xmlPorts as $port) {
            $ports[] = new Port(
                (string) $port->attributes()->portid,
                (string) $port->attributes()->protocol,
                (string) $port->state->attributes()->state,
                new Service(
                    (string) $port->service->attributes()->name,
                    (string) $port->service->attributes()->product,
                    (string) $port->service->attributes()->version
                )
            );
        }

        return $ports;
    }

    private function parseAddresses(\SimpleXMLElement $host)
    {
        $addresses = array();
        foreach ($host->xpath('./address') as $address) {
            $addresses[(string) $address->attributes()->addr] = new Address(
                (string) $address->attributes()->addr,
                (string) $address->attributes()->addrtype,
                isset($address->attributes()->vendor) ? (string) $address->attributes()->vendor : ''
            );
        }

        return $addresses;
    }
}
