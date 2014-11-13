<?php
use
    mageekguy\atoum\scripts\treemap,
    mageekguy\atoum\scripts\treemap\analyzers,
    mageekguy\atoum\scripts\treemap\categorizer
;

$script
    ->addAnalyzer(new analyzers\token())
    ->addAnalyzer(new analyzers\size())
    ->addAnalyzer(new analyzers\sloc())
    ->addAnalyzer(new treemap\analyzer\generic('commits', null, function(\splFileInfo $file) {
                $commit = exec('git log --pretty=oneline ' . escapeshellarg($file->getRealpath()) . ' | wc -l');

                return (int) trim($commit);
            }
         )
    )
;

$testsDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;

$testsCategorizer = new categorizer('Tests');

$testsCategorizer
    ->setMinDepthColor('#aae6ff')
    ->setMaxDepthColor('#000f50')
    ->setCallback(function($file) use ($testsDirectory) { return (strpos($file->getRealpath(), $testsDirectory) === 0); })
;

$vendorDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;

$vendorCategorizer = new categorizer('Vendor');

$vendorCategorizer
    ->setMinDepthColor('#FFDD99')
    ->setMaxDepthColor('#FFAA00')
    ->setCallback(function($file) use ($vendorDirectory) { return (strpos($file->getRealpath(), $vendorDirectory) === 0); })
;

$codeCategorizer = new categorizer('Code');

$codeCategorizer
    ->setMinDepthColor('#ffaac6')
    ->setMaxDepthColor('#50001b')
    ->setCallback(function($file) { return (substr($file->getFilename(), -4) == '.php'); })
;

$script
    ->addCategorizer($vendorCategorizer)
    ->addCategorizer($testsCategorizer)
    ->addCategorizer($codeCategorizer)
    ->addDirectory(__DIR__)
    
    ->setProjectName('BFW-sql')
    ->setProjectUrl('http://bfw.bulton.fr')
    ->setOutputDirectory('/home/bubu-blog/www/atoum/bfw-sql/treemap')
;