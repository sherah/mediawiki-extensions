<?php
/**
 * Wikimedia Foundation
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 */

/**
 * Generates embedded javascript charts from a tag that accepts fundraising datasets.
 *
 *
 * Usage for the tag <fundraisingChart />, which is embeddable on wiki pages:
 *
 * Tag attributes and their acceptable values:
 * charttype: pie-chart, line-chart, or bar-chart
 * dataset: a URL that provides the JSON in the correct format for the chart.
 * the formats:
 * pie-chart:  json with count, uses the "count" field to generate the pie slices. so use counts.
 * make sure each count is sorted by month.
 * bar-chart:  json with 'date' x-axis and 'count' y-axis.
 * line-chart: json with date x-axis and total y-axis.
 * map-chart:  json map data in the ext.fundraisingChart.datamaps format, usually with fillColor and associated data.
 * title: the title that will display at the top of this chart.
 *
 */

class FundraisingChart {

    //TODO: use beforePageDisplay
    static function onParserInit( Parser $parser ) {
        $parser->setHook( 'fundraisingChart', array(__CLASS__, 'frChartRender') );
    }

    /**
     * Fetch the data to be displayed in the charts.
     *
     * todo: make safer by mapping URIs to URLs
     * @param $dataset (JSON URL)
     * @return string
     */
    static function frChartDataSetFetch( $dataset ){

        $raw_json = file_get_contents($dataset);
        $d = json_decode($raw_json);

        return json_encode($d);
    }

    /**
     * Display charts
     *
     * @param $input : the value between the opening and closing tags (does not apply)
     * @param array $args : array of tag arguments
     * @param Parser $parser
     * @param PPFrame $frame
     * @return string
     */
    static function frChartRender( $input, array $args, Parser $parser, PPFrame $frame ) {
        syslog(LOG_INFO, 'the type of input is: ' . gettype($input));
        $parser->getOutput()->addModules('ext.fundraisingChart');

        if(isset($args['dataset'])){

            //make sure the incoming URL is actually a proper frdata URL.
            //if not, don't display the chart.
            $testSet = $args['dataset'];
            $acceptedURLs = array(
                "http://frdata.wikimedia.org/2012-13-fiscal-donationdata-medium-breakdown.json",
                "http://frdata.wikimedia.org/2012-13-fiscal-donation-range-breakdown.json",
                "http://frdata.wikimedia.org/countries.json",
                "http://frdata.wikimedia.org/donationdata-country-breakdown.json"
            );
            if(!in_array( $testSet, $acceptedURLs )){
                $dataset = "none";
            } else {
                $dataset = $testSet;
            }
        }else{
            $dataset = "nothing";
        };

        //create unique title
        $title = str_replace(' ', '', $args['title']);
        //get/strip the chart type
        $chartStyle = substr($args['charttype'], 0, -6);

        if($chartStyle !== 'map') {

            //associated chart data goes into its own attribute for javascript to listen to.
            $ret = Html::openElement(
                'div',
                array(
                    'id' => $chartStyle . 'ChartArea' . $title,
                    'class' => $chartStyle . 'Area',
                    'data-chartdata' => $dataset
                )
            );

            $ret .= Html::openElement('table', array('class' => $chartStyle . 'ChartTable'));

            //set title.
            $ret .= Html::openElement('tr');

            $ret .= Html::openElement('td', array('colspan' => "2"));
            $ret .= Html::element('h1', array(''), $args['title']);
            $ret .= Html::closeElement('td');

            $ret .= Html::closeElement('tr');

            $ret .= Html::openElement('tr');
            $ret .= Html::openElement('td');

            $ret .= Html::element(
                'canvas',
                array(
                    'id' => $chartStyle . 'Chart' . $title,
                    'class' => $chartStyle . 'Canvas',
                    'height' => '400',
                    'width' => '650',
                    'margin-right' => '10'
                )
            );

            $ret .= Html::closeElement('td');
            $ret .= Html::openElement('td');
            $ret .= Html::element(
                'div',
                array(
                    'id' => $chartStyle . 'Chart' . $title . 'Filter',
                    'class' => $chartStyle . 'Filter'
                )
            );

            $ret .= Html::closeElement('td');
            $ret .= Html::closeElement('tr');

            $ret .= Html::closeElement('table');

        }
        else {

            $ret = Html::openElement(
                'div',
                array(
                    'id' => 'mapChartArea' . $title,
                    'class' => 'mapArea',
                    'data-chartdata' => $dataset
                )
            );
            //set title.
            $ret .= Html::openElement('tr');

            $ret .= Html::openElement('td', array('colspan' => "2"));
            $ret .= Html::element('h1', array(''), $args['title']);
            $ret .= Html::closeElement('td');

            $ret .= Html::closeElement('tr');

            $ret .= Html::closeElement('div');

        }

        $ret .= Html::closeElement('div');
        return $ret;
    }
}


