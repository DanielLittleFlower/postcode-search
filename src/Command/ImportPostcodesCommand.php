<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;

#[AsCommand(
    name: 'ImportPostcodesCommand',
    description: 'Downloads and imports UK postcodes'
)]
class ImportPostcodesCommand extends Command
{
    private const POSTCODES_URL = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';

    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this->setDescription('Downloads and imports UK postcodes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // Download the ZIP archive containing the CSV file
        $archivePath = tempnam(sys_get_temp_dir(), 'postcodes');
        
        $client = new \GuzzleHttp\Client();
        $client->request('GET', self::POSTCODES_URL, ['sink' => $archivePath]);
    
        // Extract the CSV file from the archive
        $zip = new \ZipArchive();
        if ($zip->open($archivePath) !== true) {
            $output->writeln('Error: Failed to open ZIP archive.');
            return Command::FAILURE;
        }
        
        $zip->extractTo(sys_get_temp_dir(), 'Data/ONSPD_NOV_2022_UK.csv');
        $zip->close();

        $csvFilePath = sys_get_temp_dir() . '/Data/ONSPD_NOV_2022_UK.csv';
    
        // Import postcodes into database
        $statement = $this->connection->prepare('INSERT INTO postcodes (postcode, latitude, longitude) VALUES (:postcode, :latitude, :longitude)');


        $handle = fopen($csvFilePath, 'r');
        if ($handle !== false) {
            $count = 0;
            while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
                $postcode = $data[0];
                $latitude = $data[42]; 
                $longitude = $data[43];
                
                // Invalid latitude/longitude value, skip this row
                if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    $output->writeln(sprintf('Skipped postcode: %s, latitude: %s, longitude: %s', $postcode, $latitude, $longitude));
                    continue;
                }
                
                try {
                    $statement->executeQuery([
                        'postcode' => $postcode,
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]);
                    $count++;
                    //$output->writeln(sprintf('postcode: %s, latitude: %s, longitude: %s', $postcode, $latitude, $longitude));

                } catch (\Exception $e) {
                    $output->writeln(sprintf('Error: %s', $e->getMessage()));
                }
                
            }
            fclose($handle);

            // Remove downloaded files
            unlink($archivePath);
            unlink($csvFilePath);

            $output->writeln(sprintf('Imported %d postcodes', $count));

            return Command::SUCCESS;
        } else {
            $output->writeln('Error: Failed to open CSV file.');
            return Command::FAILURE;
        }
    }
}

