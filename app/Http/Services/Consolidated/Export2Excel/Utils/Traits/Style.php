<?php

namespace App\Http\Services\Consolidated\Export2Excel\Utils\Traits;

trait Style
{
    /**
     * The excel header and footer background color applications, if to pply the legen colors
     *
     * @var bool
     */
    protected $applyLegendsColor = false;

    public function applyLegendsColor($applyLegendsColor)
    {
        $this->applyLegendsColor = $applyLegendsColor;
    }

    /**
     * Excel file, header of data styling.
     *
     * @param  string  $range column range
     */
    protected function headerStyle(string $range, $lastCol = 'AN')
    {
        $this->excel->getActiveSheet()
            ->getStyle($range)
            ->applyFromArray(
                [
                    'font' => [
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => '003300'],
                    ],
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'A19C9C'],
                        ],
                    ],
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'CCF2EC'],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]
            );

        if ($this->applyLegendsColor) {
            $this->applylegendColors($this->headerRow);
        }

        $this->excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20.5);

        $this->excel->getActiveSheet()->freezePane('C2');

        // Auto size column
        foreach (range('A', $lastCol) as $columnID) {
            $this->excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        // $totalRow = 1 + $this->recordsCount();
        // //formatting of margin percentage
        //       $this->excel->getActiveSheet()->setColumnFormat([
        //           "H2:H$totalRow" => '0',
        //           "I2:I$totalRow" => '0'
        //       ]);
    }

    /**
     * Excel file, footer of data styling.
     *
     * @param  string  $range column range
     */
    protected function footerStyle(string $range)
    {
        $this->excel->getActiveSheet()
            ->getStyle($range)
            ->applyFromArray(
                [
                    'font' => [
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => '003300'],
                    ],
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'A19C9C'],
                        ],
                    ],
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'CCF2EC'],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]
            );

        if ($this->applyLegendsColor) {
            $this->applylegendColors(($this->recordsCount() + ($this->headerRow + 1)));
        }

        $this->excel->getActiveSheet()->getRowDimension((2 + $this->recordsCount()))->setRowHeight(20.5);

    }

    /**
     * Excel file, body/list of data styling.
     */
    protected function bodyStyle()
    {
        $this->excel->getActiveSheet()
            ->getStyle('A2:AC'.(1 + $this->recordsCount()))
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // for($i = 1; $i <= $this->recordsCount(); $i ++) {
        //     $this->excel->getActiveSheet()->getRowDimension($i + 1)->setRowHeight(20);
        // }

        $styleArray = [
            'borders' => [
                'top' => [
                    'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => ['argb' => '7e7e7e'],
                ],
            ],
        ];

        $i = 1;
        $cur_rev_tracker = null;
        $last_col = $this->excel->getActiveSheet()->getHighestDataColumn();
        $cur_row = $this->headerRow + 1;
        foreach ($this->records as $record) {
            if ($cur_rev_tracker != null && $record['revenue_tracker_id'] != $cur_rev_tracker) {
                $this->excel->getActiveSheet()->getStyle('A'.$cur_row.':'.$last_col.$cur_row)->applyFromArray($styleArray);
            }
            $cur_rev_tracker = $record['revenue_tracker_id'];

            $this->excel->getActiveSheet()->getRowDimension($i + 1)->setRowHeight(20);
            $i++;
            $cur_row++;
        }
        // \Log::info($this->records);
        // \Log::info($this->recordsCount());
    }

    /**
     * Excel file, legend descriptio header styling.
     *
     * @param  string  $range column range
     */
    protected function legendsStyle(string $range)
    {
        $this->excel->getActiveSheet()
            ->getStyle($range)
            ->applyFromArray(
                [
                    'font' => [
                        // 'bold' => true,
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => 'B43037'],
                    ],
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'A19C9C'],
                        ],
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'A19C9C'],
                        ],
                    ],
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFC7CE'],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]
            );

        $this->excel->getActiveSheet()->getRowDimension((4 + $this->recordsCount()))->setRowHeight(20.5);

    }

    /**
     * Excel file, Alignment of all data, vertical center.
     */
    protected function alignment($lastCol)
    {
        $this->excel->getActiveSheet()
            ->getStyle('A1:'.$lastCol.(1 + $this->recordsCount() + 35))
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    /**
     * Appl the legend colors
     */
    protected function applylegendColors(int $row)
    {
        $color = 'F2F3F5';

        foreach ($this->columnsRange as $index => $column) {
            $colorIndx = $index - 7;
            if ($colorIndx >= 0) {
                $color = (array_key_exists($colorIndx, $this->legends)) ? strtoupper(str_replace('#', '', $this->legends[$colorIndx][1])) : 'E8EAEC';
            }

            $this->excel->getActiveSheet()
                ->getStyle($column.$row)
                ->applyFromArray(
                    [
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => $color],
                        ],
                    ]
                );
        }
    }
}
