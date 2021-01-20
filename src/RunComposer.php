<?php


namespace SecurityAdviser\Console;


use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunComposer extends Command
{
    protected function configure()
    {
        $this->setName('run:check')
            ->setAliases(['r', 'run'])
            ->setDescription('Check for security advisories for the packages in your composer.json')
            ->addOption('dev', 'D', InputOption::VALUE_NONE, 'If require-dev should be checked as well')
            ->addOption('global', 'G', InputOption::VALUE_NONE, 'If you wish to check your global composer requires.')
            ->addOption('directory', ['d', 'dir'], InputOption::VALUE_REQUIRED, 'The folder you wish to check, relative to the current folder.')
            ->addOption('first', ['f'], InputOption::VALUE_NONE, 'If you want to check view only the latest advisory for each package.');
            
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        if($input->getOption('directory')){
            $dir = getcwd().'/'.$input->getOption('directory');
        }
        elseif($input->getOption('global')){
            $global = exec('composer config home');
            
            $dir = $global[0];
        }
        else $dir = getcwd().'/';
        
        if(!file_exists($dir.'composer.json')){
            $output->write('No composer.json file found.');
            
            return 1;
        }
        
        $composer_json = file_get_contents($dir.'composer.json');
        
        $json = json_decode($composer_json, true);
        
        $client = new Client();
        
        if($input->getOption('dev')){
            if(!array_key_exists('require-dev', $json)){
                $output->write('There is no require-dev.'.PHP_EOL);
                return 1;
            }
        }
    
        if(!array_key_exists('require', $json)){
            $output->write('There is no require.'.PHP_EOL);
            return 1;
        }
    
        foreach ($json['require'] as $key => $value) {
            if($output->isVerbose()){
                $output->write('Checking ' . $key . '...' . PHP_EOL);
            }
            $response = $client->request('GET', 'https://packagist.org/api/security-advisories/?packages[]='.$key);
            $response_json = json_decode($response->getBody(), true);
            
            if(!array_key_exists($key, $response_json['advisories'])){
                if($output->isVeryVerbose()){
                    $output->write('No advisories for ' . $key . ' Found'.PHP_EOL);
                }
            }
            else{
                if($input->getOption('first')){
                    foreach ($response_json['advisories'] as $advisory => $list) {
                        $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                        $first = array_values($list)[0];
                        
                            $output->write($first['title'] . ' | ' . $first['link'] . PHP_EOL . '| Affects: ' . $first['affectedVersions'] . PHP_EOL . PHP_EOL);
                            
                        $output->write(PHP_EOL);
                    }
                }
                else {
                    foreach ($response_json['advisories'] as $advisory => $list) {
                        $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                        foreach ($list as $item) {
                            $output->write($item['title'] . ' | ' . $item['link'] . PHP_EOL . '| Affects: ' . $item['affectedVersions'] . PHP_EOL . PHP_EOL);
            
                        }
                        $output->write(PHP_EOL);
                    }
                }
            }
            
            
            
        }
        
        if($input->getOption('dev')){
            foreach ($json['require-dev'] as $key => $value) {
                if($output->isVerbose()){
                    $output->write('Checking ' . $key . '...' . PHP_EOL);
                }
                $response = $client->request('GET', 'https://packagist.org/api/security-advisories/?packages[]='.$key);
                $response_json = json_decode($response->getBody(), true);
        
                if(!array_key_exists($key, $response_json['advisories'])){
                    if($output->isVeryVerbose()){
                        $output->write('No advisories for ' . $key . ' Found'.PHP_EOL);
                    }
                }
                else{
                    if($input->getOption('first')){
                        foreach ($response_json['advisories'] as $advisory => $value) {
                            $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                            $first = array_values($value)[0];
    
                            $output->write($first['title'] . ' | ' . $first['link'] . PHP_EOL . '| Affects: ' . $first['affectedVersions'] . PHP_EOL . PHP_EOL);
                            $output->write(PHP_EOL);
                        }
                    }
                    else {
                        foreach ($response_json['advisories'] as $advisory => $value) {
                            $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                            foreach ($value as $item) {
                                $output->write($item['title'] . ' | ' . $item['link'] . PHP_EOL . '| Affects: ' . $item['affectedVersions'] . PHP_EOL . PHP_EOL);
                        
                            }
                            $output->write(PHP_EOL);
                        }
                    }
                }
        
        
        
            }
        }
        
        
        
        return 0;
        
    }
    
}
