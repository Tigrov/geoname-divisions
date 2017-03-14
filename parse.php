<?php
/**
 * Parser to get list of divisions and subdivisions with ISO-3166-2 codes in CSV format from http://www.geonames.org/
 *
 * To execute run the command in shell `php parse.php`
 * The result will be saved in files `result/divisions.csv`, `result/subdivisions.csv`
 *
 * @link https://github.com/tigrov/whois
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

require(__DIR__ . '/simple_html_dom.php');

define('RESULT_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'result');
define('GEONAME_URL', 'http://www.geonames.org/{country_code}/administrative-division-.html');
define('NO_LONGER_EXISTS', 'no longer exists:');
define('CSV_DELIMITER', ';');

// ISO 3166-1 alpha-2 country codes
$countryCodes = ['AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE',
                 'BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BV','BW','BY','BZ','CA','CC','CD',
                 'CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM',
                 'DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF',
                 'GG','GH','GI','GL','GM','GN','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU',
                 'ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI','KM','KN',
                 'KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME',
                 'MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA',
                 'NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM',
                 'PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI',
                 'SJ','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK',
                 'TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','UM','US','UY','UZ','VA','VC','VE','VG','VI',
                 'VN','VU','WF','WS','XK','YE','YT','ZA','ZM','ZW'];

// CSV file headers
$divisionsHeader = ['ISO-3166-1', 'ISO-3166-2', 'Fips', 'GN', 'Geoname ID', 'Name of Subdivision', 'Wikipedia', 'Type ID', 'Type', 'Geoname ID of Capital', 'Capital', 'Population', 'lang', 'continent', 'From', 'Till'];
$subdivisionsHeader = ['ISO-3166-1', 'ISO-3166-2', 'ISO Region', 'Fips', 'GN', 'Geoname ID', 'Name of Subdivision', 'Wikipedia', 'Type ID', 'Type', 'Geoname ID of Capital', 'Capital', 'Population', 'lang', 'continent', 'From', 'Till'];

$divisionsCsv = fopen(RESULT_DIR . DIRECTORY_SEPARATOR . 'divisions.csv', 'w');
$subdivisionsCsv = fopen(RESULT_DIR . DIRECTORY_SEPARATOR . 'subdivisions.csv', 'w');

fputcsv($divisionsCsv, $divisionsHeader, CSV_DELIMITER);
fputcsv($subdivisionsCsv, $subdivisionsHeader, CSV_DELIMITER);

foreach ($countryCodes as $countryCode) {
    echo $countryCode . '...' . PHP_EOL;

    $url = str_replace('{country_code}', $countryCode, GEONAME_URL);
    $html = file_get_html($url, null, null, null);

    /**
     * @var simple_html_dom_node $table
     * @var simple_html_dom_node[] $trNodes
     * @var simple_html_dom_node[] $tdNodes
     * @var simple_html_dom_node $nameNode
     * @var simple_html_dom_node $typeNode
     */

    // Parse divisions
    if ($table = $html->find('table[id=subdivtable1]', 0)) {
        $trNodes = $table->childNodes();
        if ($trNodes > 1) {
            unset($trNodes[0]);
            foreach ($trNodes as $tr) {
                $tdNodes = $tr->childNodes();
                if ($tdNodes[0]->text() == NO_LONGER_EXISTS) {
                    // Skip old values
                    break;
                }

                // Skip territories
                if ($geonameId = GetGeonameId($tdNodes[4])) {
                    $row = [
                        $countryCode,
                        $tdNodes[1]->text(),
                        $tdNodes[2]->text(),
                        $tdNodes[3]->text(),
                        $geonameId,
                        $tdNodes[4]->firstChild()->firstChild()->text(),
                        GetWikipediaLink($tdNodes[4]),
                        GetTypeId($tdNodes[5]),
                        $tdNodes[5]->text(),
                        GetGeonameId($tdNodes[6]),
                        $tdNodes[6]->text(),
                        str_replace(',', '', $tdNodes[7]->text()),
                        $tdNodes[8]->text(),
                        $tdNodes[9]->text(),
                        $tdNodes[10]->text(),
                        $tdNodes[11]->text(),
                    ];

                    $row = array_map('trim', $row);

                    fputcsv($divisionsCsv, $row, CSV_DELIMITER);
                }
            }
        }
    }

    // Parse subdivisions
    if ($table = $html->find('table[id=subdivtable2]', 0)) {
        $trNodes = $table->childNodes();
        if ($trNodes > 1) {
            unset($trNodes[0]);
            foreach ($trNodes as $tr) {
                $tdNodes = $tr->childNodes();
                if ($tdNodes[0]->text() == NO_LONGER_EXISTS) {
                    // Skip old values
                    break;
                }

                if ($geonameId = GetGeonameId($tdNodes[5])) {
                    $row = [
                        $countryCode,
                        $tdNodes[1]->text(),
                        $tdNodes[2]->text(),
                        $tdNodes[3]->text(),
                        $tdNodes[4]->text(),
                        $geonameId,
                        $tdNodes[5]->firstChild()->firstChild()->text(),
                        GetWikipediaLink($tdNodes[5]),
                        GetTypeId($tdNodes[6]),
                        $tdNodes[6]->text(),
                        GetGeonameId($tdNodes[7]),
                        $tdNodes[7]->text(),
                        str_replace(',', '', $tdNodes[8]->text()),
                        $tdNodes[9]->text(),
                        $tdNodes[10]->text(),
                        $tdNodes[11]->text(),
                        $tdNodes[12]->text(),
                    ];

                    $row = array_map('trim', $row);

                    fputcsv($subdivisionsCsv, $row, CSV_DELIMITER);
                }
            }
        }
    }
}

fclose($divisionsCsv);
fclose($subdivisionsCsv);

/**
 * @param simple_html_dom_node $node
 * @return null|integer
 */
function GetGeonameId($node) {
    if ($geonameNode = $node->find('a[href*=geonames.org]', 0)) {
        list(,,, $geonameId) = explode('/', $geonameNode->href);

        return $geonameId;
    }

    return null;
}

/**
 * @param simple_html_dom_node $node
 * @return null|string
 */
function GetWikipediaLink($node) {
    return ($wikipediaNode = $node->find('a[href*=wikipedia]', 0))
        ? $wikipediaNode->href
        : null;
}

/**
 * @param simple_html_dom_node $node
 * @return null|integer
 */
function GetTypeId($node) {
    if ($typeNode = $node->find('a[href*=typeId]', 0)) {
        list(,, $tagId) = explode('=', $typeNode->href);

        return $tagId;
    }

    return null;
}