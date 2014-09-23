<?php

/*
Sample atoum configuration file to have code coverage in html format and the treemap.
Do "php path/to/test/file -c path/to/this/file" or "php path/to/atoum/scripts/runner.php -c path/to/this/file -f path/to/test/file" to use it.
*/

use \mageekguy\atoum;

// HTML

/*
Please replace in next line /path/to/destination/directory by your destination directory path for html files.
*/
$coverageHtmlField = new atoum\report\fields\runner\coverage\html('BFW-SQL', __DIR__.'/report');

/*
Please replace in next line http://url/of/web/site by the root url of your code coverage web site.
*/
$coverageHtmlField->setRootUrl('http://test.bulton.fr/bfw-sql/');

// Treemap (not mandatory)

/*
Please replace in next line /path/to/destination/directory by your destination directory path for html files.
*/
$coverageTreemapField = new atoum\report\fields\runner\coverage\treemap('BFW-SQL', __DIR__.'/treemap');

/*
Please replace in next line http://url/of/treemap by the root url of your treemap web site.
*/
$coverageTreemapField
        ->setTreemapUrl('http://test.bulton.fr/bfw-sql/treemap')
        ->setHtmlReportBaseUrl($coverageHtmlField->getRootUrl())
;

$script
        ->addDefaultReport()
                ->addField($coverageHtmlField)
                ->addField($coverageTreemapField)
;