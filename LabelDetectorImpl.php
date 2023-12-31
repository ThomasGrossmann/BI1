<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/ILabelDetector.php';

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class LabelDetectorImpl implements ILabelDetector
{
    private $client;

    public function __construct()
    {
        $env = parse_ini_file('.env');
        $this->client = new ImageAnnotatorClient(
            ['credentials' => $env['CREDENTIALS_PATH']]
        );
    }

    public function analyze($remoteFullPath, $maxLabels = 10, $minConfidenceLevel = 0.8): array
    {
        $image = file_get_contents($remoteFullPath);
        $response = $this->client->labelDetection($image);
        $labels = $response->getLabelAnnotations();
        $result = [];
        if ($labels) {
            foreach ($labels as $label) {
                if ($label->getScore() >= $minConfidenceLevel) {
                    $result[$label->getDescription()] = $label->getScore();
                }
            }
        }
        return $result;
    }
}
