<?php

namespace App\Http\Services\Charts\Utils;

use File;

trait JSonData
{
    /**
     * Process on generating json data.
     *
     * @var string
     */
    protected function generateJSon($reject_rate)
    {
        // create json file
        $json = '';
        $start_fake_json = '{
            series: '.json_encode($this->series[$reject_rate]);

        $json .= $start_fake_json;

        $end_fake_json = "
                ,chart: {
                    type: '".$this->type."',
                    events: {
                        load: function () {
                            var label = this.renderer.label(\"STATUS: ".strtoupper($reject_rate)."\")
                            .css({
                                width: '400px',
                                fontSize: '12px',
                                textAlign: 'right'
                            })
                            .add();
                            label.align(Highcharts.extend(label.getBBox(), {
                                align: 'right',
                                x: 0, // offset
                                verticalAlign: 'bottom',
                                y: 40 // set the right offset
                            }), null, 'spacingBox');
                        }
                    },
                    spacingBottom: 60
                }
                ,title: {
                    text: 'Lead Reactor Leads Graph'
                }
                ,xAxis:{
                    categories: ".json_encode($this->categories[$reject_rate]).",
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize:'6px'
                        }
                    }
                }
                ,yAxis: {
                    allowDecimals: false,
                    min: 0,
                    title: {
                        text: 'Number of Leads'
                    },
                    labels: {
                        style: {
                            fontSize:'6px'
                        }
                    }
                }
                ,legend: {
                    useHTML:true,
                    symbolHeight: 4,
                    symbolWidth: 4,
                    symbolRadius: 0,
                    enabled: true,
                    itemStyle: {
                        fontWeight: 'bold',
                        fontSize: '5px',
                        verticalalign:'top'
                    }
                }
                ,plotOptions: {
                    column: {
                        stacking: 'normal',
                        groupPadding: ".$this->group_padding[$reject_rate].',
                        borderWidth: 0,
                        maxPointWidth: 3
                    },
                    series: {
                        pointPadding: 0
                    }
                }
                ,navigator : {
                    enabled : false
                },rangeSelector:{
                    enabled:false
                },scrollbar : {
                    enabled : false
                },credits: {
                    enabled: false
                }
            }';

        $json .= $end_fake_json;
        $this->json = $json;
    }

    /**
     * Process on saving json data as file.
     */
    protected function saveJSon()
    {
        if (! empty($this->json)) {
            File::put($this->config['infile'], $this->json);
        }
    }
}
