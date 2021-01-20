<?php


namespace SecurityAdviser\Console;


use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunSingle extends Command
{
    
    protected function configure()
    {
        $this->setName('run:single')
             ->setAliases(['rs', 'single'])
             ->setDescription('Get security advisories for a single package.')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the package.')
             ->addOption('first', ['f'], InputOption::VALUE_NONE, 'If you want to only get the latest advisory.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        
        $client = new Client(['http_errors' => false]);
        
        $package_check = $client->request('GET', 'https://repo.packagist.org/p/' . $name . '.json');
        
        if ($package_check->getStatusCode() != 200) {
            $output->write('Package "' . $name . '" does not exist.' . PHP_EOL);
            return 1;
        }
        
        $response = $client->request('GET', 'https://packagist.org/api/security-advisories/?packages[]=' . $name);
        
        $response_json = json_decode($response->getBody(), true);
        
        if ($output->isVerbose()) {
            $output->write('Checking ' . $name . '...' . PHP_EOL);
        }
        
        if (!array_key_exists($name, $response_json['advisories'])) {
                $output->write('No advisories for ' . $name . ' Found' . PHP_EOL);
                return 1;
        } else {
            if ($input->getOption('first')) {
                foreach ($response_json['advisories'] as $advisory => $list) {
                    $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                    $first = array_values($list)[0];
                    
                    $output->write($first['title'] . ' | ' . $first['link'] . PHP_EOL . '| Affects: ' . $first['affectedVersions'] . PHP_EOL . PHP_EOL);
                    
                    $output->write(PHP_EOL);
                }
            } else {
                foreach ($response_json['advisories'] as $advisory => $list) {
                    $output->write($advisory . ':' . PHP_EOL . PHP_EOL);
                    foreach ($list as $item) {
                        $output->write($item['title'] . ' | ' . $item['link'] . PHP_EOL . '| Affects: ' . $item['affectedVersions'] . PHP_EOL . PHP_EOL);
                        
                    }
                    $output->write(PHP_EOL);
                }
            }
            
            return 0;
            
        }
    }
}
