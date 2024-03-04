<?php

namespace App\Http\Services\Consolidated\Export2Excel\Utils;

use Carbon\Carbon;

class Excel extends \App\Http\Services\Factories\ExportExcel
{
    /**
     * Excel style.
     */
    use Traits\Style;

    /**
     * Default
     */
    protected $records;

    protected $footer;

    protected $legends;

    protected $headerRow = 1;

    protected $ab_testing = [];

    protected $ab_columns = [];

    protected $ab_testing_row_counts = 3;

    protected $filters;

    protected $perSubIDRecords;

    protected $subIDSummaryRecords;

    /**
     * The last row name that will be field.
     * For now this is static and should be change here if the data was reduce or extended.
     *
     * @var string
     */
    protected $lastCol = 'AC';

    protected $columnsRange = [];

    /**
     * Set the data for header, and provide the row number wher the header will occupied.
     */
    public function setRecordsHeader(array $header, int $row = 1)
    {
        $this->header = $header;
        $this->headerRow = $row;
        $this->createColumnsRange(count($header));
        $this->lastCol = end($this->columnsRange);

        return $this;
    }

    /**
     * Set the data for footer.
     */
    public function setRecordsFooter(array $footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * Set the data/list.
     */
    public function setRecords(array $records)
    {
        // \Log::info($records);
        // \Log::info('test');
        // array_map(function($value) {

        // }, $records);

        array_walk_recursive($records, 'self::stringifyZero');
        // \Log::info($records);
        $this->records = $records;

        return $this;
    }

    public function setPerSubIDRecords($records)
    {
        array_walk_recursive($records, 'self::stringifyZero');

        $this->perSubIDRecords = $records;

        return $this;
    }

    public function setSubIDSummaryRecords($records)
    {
        array_walk_recursive($records, 'self::stringifyZero');
        $this->subIDSummaryRecords = $records;

        return $this;
    }

    public static function stringifyZero(&$item, $key)
    {
        // \Log::info($key.' - '. $item);
        $keys = ['revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'];
        if (! in_array($key, $keys) && (trim($item) == '' || $item == null || $item == null)) {
            $item = 0;
        }
        $item = is_numeric($item) && ! in_array($key, $keys) ? (float) sprintf('%0.2f', $item) : (string) $item;

        // \Log::info($key.' - '. $item);
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Records/Lists count.
     */
    public function recordsCount(): int
    {
        return count($this->records) + $this->ab_testing_row_counts;
    }

    /**
     * Set the data of legends with descriptions.
     */
    public function setLegendsWithDescription(array $legends)
    {
        $this->legends = $legends;

        // printR($this->legends);
        return $this;
    }

    /**
     * Generate now the sheet's content.
     */
    public function generateSheet($sheet)
    {
        /** BOF STEP 1 - HEADER **/
        // Add the header
        $sheet->row($this->headerRow, $this->header);
        // Add style
        $this->headerStyle('A'.$this->headerRow.':'.$this->lastCol.$this->headerRow, $this->lastCol);
        /** EOF STEP 1 - HEADER **/

        /** BOF STEP 2 - RECORDS/LISTS **/
        // Add the lists
        // $this->headerRow + 1 : The row that will be occupied by the records, below header row.
        // \Log::info($this->headerRow + 1);
        // \Log::info($this->records);
        $sheet->fromArray($this->records, null, 'A'.($this->headerRow + 1), true, false);
        // Add style
        $this->bodyStyle();
        /** EOF STEP 2 - RECORDS/LISTS **/

        /** BOF AB TESTING **/
        if ($this->ab_testing) {
            $this->createABTesting($sheet);
        }

        /** BOF STEP 3 - FOOTER **/
        if ($this->footer) {
            // Add the footer.
            // $this->recordsCount() + $this->headerRow + 1 : The row that will be occupied by the footer, below lists.
            $sheet->row($this->recordsCount() + ($this->headerRow + 1), $this->footer);
            // Add style
            $this->footerStyle('A'.($this->recordsCount() + ($this->headerRow + 1)).':'.$this->lastCol.($this->recordsCount() + 2));
        }
        /** EOF STEP 3 - FOOTER **/

        /** BOF STEP 4 - LEGENDS **/
        if ($this->legends) {
            // Add the legends header
            // $this->recordsCount() + $this->headerRow + 3 : The row that will be occupied by the legend header, below lists footer.
            $sheet->row($this->recordsCount() + ($this->headerRow + 3), ['Legends']);
            // Add style
            $this->legendsStyle('A'.($this->recordsCount() + ($this->headerRow + 3)).':'.$this->lastCol.($this->recordsCount() + 4));

            // Add the legends with descriptions lists
            $this->createLegendsDescritions($sheet);
        }
        /** EOF STEP 4 - LEGENDS **/

        /** BOF STEP 5 - OTHERS **/
        $this->alignment($this->lastCol);
        // ....
        /** BOF STEP 4 - OTHERS **/
    }

    /**
     * Fill worksheet from values in array
     *
     * @param  array  $source Source array
     * @param  mixed  $nullValue Value in source array that stands for blank cell
     * @param  string  $startCell Insert array starting from this cell address as the top left coordinate
     * @param  bool  $strictNullComparison Apply strict comparison when testing for null values in the array
     *
     * @throws PhpExcelException
     */
    public function fromArray($sheet): Worksheet
    {
        foreach ($this->records as &$row) {
            if (is_array($row)) {
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $row[$key] = \PhpOffice_PHPExcel_Shared_Date::PHPToExcel($value);
                    }
                }
            }
        }
        try {
            $sheet->fromArray($this->records, null, 'A'.($this->headerRow + 1), false);
        } catch (\PhpOffice_PHPExcel_Exception $ex) {
            throw new \PhpExcelException('Unable to paste the array to worksheet', $ex->getCode(), $ex);
        }

        return $this;
    }

    /**
     * Excel header column range
     */
    protected function createColumnsRange(int $count, int $counter = 0, string $firstLetters = ''): array
    {
        $firstLettersIndx = 0;
        if ($firstLetters) {
            $firstLettersIndx = (array_search($firstLetters, $this->columnsRange));
            $firstLettersIndx++;
        }

        // Iterate over 26 letters.
        foreach (range('A', 'Z') as $letter) {
            // Paste the $first_letters before the next.
            $column = $firstLetters.$letter;

            // Add the column to the final array.
            $this->columnsRange[] = $column;

            $counter++;

            // If it was the end column that was added, return the columnsRange.
            if ($counter == $count) {
                return $this->columnsRange;
            }
        }

        if ($counter < $count) {
            // Add the column children.
            $newColumns = $this->createColumnsRange($count, $counter, $this->columnsRange[$firstLettersIndx]);
            // Merge the new columnsRange which were created with the final columnsRange array.
            $columnsRange = array_merge($this->columnsRange, $newColumns);
        }

        return $columnsRange;
    }

    /**
     * Create the legends lists.
     */
    protected function createLegendsDescritions(LaravelExcelWorksheet $sheet)
    {
        // $this->recordsCount() + $this->headerRow + 4 : The row that will be occupied by the legends with description, below legend header.
        $count = $this->recordsCount() + ($this->headerRow + 4);
        // Go through the array and insert it.
        foreach ($this->legends as $details) {
            $sheet->mergeCells('A'.$count.':B'.$count);
            $sheet->mergeCells('C'.$count.':'.end($this->columnsRange).$count);
            $sheet->row($count, $details);
            $count++;
        }
        // \Log::info($this->legends);
    }

    public function setABTesting($ab_testing)
    {
        $this->ab_testing = $ab_testing;
    }

    public function setABColumns($ab_columns)
    {
        $this->ab_columns = $ab_columns;

        return $this;
    }

    public function createABTesting($sheet)
    {
        //\Log::info($this->ab_testing);
        $today = ['Total', 'Total Vs. Yesterday', '', '', '', '', ''];
        $thirty = ['Total', 'Total Vs. 30-Day ', '', '', '', '', ''];

        $colors = [
            'red' => '#d60707',
            'yellow' => '#ffc513',
            'green' => '#37960f',
        ];

        $today_cell = $this->recordsCount() - 2 + ($this->headerRow + 1);
        $thirty_cell = $this->recordsCount() - 1 + ($this->headerRow + 1);
        $sheet->cells("A$today_cell:B$thirty_cell", function ($cells) {
            $cells->setFont([
                'bold' => true,
            ]);
        });

        $col = 'H';
        foreach ($this->ab_columns as $column) {
            $today[] = $this->ab_testing['yesterday'][$column]['r'];
            $thirty[] = $this->ab_testing['30'][$column]['r'];

            $sheet->cell($col.$today_cell, function ($cell) use ($column, $colors) {
                if ($this->ab_testing['yesterday'][$column]['c'] != '') {
                    $cell->setFontColor($colors[$this->ab_testing['yesterday'][$column]['c']]);
                }
            });

            $sheet->cell($col.$thirty_cell, function ($cell) use ($column, $colors) {
                if ($this->ab_testing['30'][$column]['c'] != '') {
                    $cell->setFontColor($colors[$this->ab_testing['30'][$column]['c']]);
                }
            });

            $col++;
        }

        $sheet->row($today_cell, $today);

        $sheet->row($thirty_cell, $thirty);
    }

    public function generatePerSubIDSheet($sheet)
    {
        // \Log::info($this->filters);
        /** BOF STEP 1 - HEADER **/
        // Add the header
        $sheet->row($this->headerRow, $this->header);

        $sheet
            ->getStyle('A1:AN1')
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

        $sheet->getRowDimension('1')->setRowHeight(20.5);

        $sheet->freezePane('C2');

        // Auto size column
        foreach (range('A', 'AN') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        /** BOF STEP 2 - RECORDS/LISTS **/

        // Add the lists
        // \Log::info($this->perSubIDRecords);
        $recordCount = count($this->perSubIDRecords);
        $curTrackerCode = '';
        $curPubSubIds = '';
        $rows = [];
        $rowNumber = 2;
        $begin = 2;
        $summaryRow = [];

        $all_views = 0;
        $c1_revenue = 0;
        $c2_revenue = 0;
        $c3_revenue = 0;
        $c4_revenue = 0;
        $c1_views = 0;
        $c2_views = 0;
        $c3_views = 0;
        $c4_views = 0;
        $mid_views = 0;
        $pd_views = 0;
        $tib_views = 0;
        $iff_views = 0;
        $rex_views = 0;
        $cpa_views = 0;
        $lp_views = 0;

        foreach ($this->perSubIDRecords as $tracker) {
            // \Log::info($curTrackerCode.' = '.$this->genCode($tracker));
            if ($curTrackerCode != '' && $curTrackerCode != $this->genCode($tracker)) {
                //Get Total
                $rows[] = [
                    '', '', '', '', '', '', '',
                    '=SUM(H'.$begin.':H'.($rowNumber - 1).')', //All CLicks
                    '=SUM(I'.$begin.':I'.($rowNumber - 1).')', //Survey Takers
                    "=M$rowNumber/H$rowNumber", //Cost / All Clicks
                    "=I$rowNumber/H$rowNumber", //Survey Takers / Clicks
                    '=SUM(L'.$begin.':L'.($rowNumber - 1).')', //Total Revenue
                    '=SUM(M'.$begin.':M'.($rowNumber - 1).')', // Cost
                    "=(L$rowNumber - M$rowNumber) / L$rowNumber", //Margin
                    "=L$rowNumber / H$rowNumber", //Revenue / All Clicks
                    "=L$rowNumber / I$rowNumber", //Revenue / Survye Takers
                    '=SUM(Q'.$begin.':Q'.($rowNumber - 1).')', //All Inbox
                    "=Q$rowNumber / I$rowNumber", //All Inbox Survey Takers
                    '=SUM(S'.$begin.':S'.($rowNumber - 1).')', //All Coreg Revenue
                    "=S$rowNumber / $all_views", //All Coreg Revenue / All Views
                    "=$c1_revenue / $c1_views", //Coreg P1 Revenue / Views
                    "=$c2_revenue / $c2_views", //Coreg P2 Revenue / Views
                    "=$c3_revenue / $c3_views", //Coreg P3 Revenue / Views
                    "=$c4_revenue / $c4_views", //Coreg P4 Revenue / Views
                    '=SUM(Y'.$begin.':Y'.($rowNumber - 1).')', //All Mid Paths Revenue
                    "=Y$rowNumber / $mid_views", //All Mid Paths Revenue / Views
                    '=SUM(AA'.$begin.':AA'.($rowNumber - 1).')', //Permission Data Revenue
                    "=AA$rowNumber / $pd_views", //Permission Data Revenue / Views
                    '=SUM(AC'.$begin.':AC'.($rowNumber - 1).')', //Tiburon Revenue
                    "=AC$rowNumber / $tib_views", //Tiburon Revenue / Views
                    '=SUM(AE'.$begin.':AE'.($rowNumber - 1).')', //Ifficient Revenue
                    "=AE$rowNumber / $iff_views", //Ifficient Revenue / Views
                    '=SUM(AG'.$begin.':AG'.($rowNumber - 1).')', //Rexadz Revenue
                    "=AG$rowNumber / $rex_views", //Rexadz Revenue / Views
                    '=SUM(AI'.$begin.':AI'.($rowNumber - 1).')', //Push Revenue
                    "=AI$rowNumber / I$rowNumber", //Push Revenue / ST
                    '=SUM(AK'.$begin.':AK'.($rowNumber - 1).')', //CPA Revenue
                    "=AK$rowNumber / $cpa_views", //CPA Revenue / Views
                    '=SUM(AM'.$begin.':AM'.($rowNumber - 1).')', //Last Page Revenue
                    "=AM$rowNumber / $lp_views", //Last Page Revenue / Views
                ];

                $summaryRow[] = $rowNumber;

                $rowNumber++;
                $begin = $rowNumber;
                $all_views = 0;
                $c1_revenue = 0;
                $c2_revenue = 0;
                $c3_revenue = 0;
                $c4_revenue = 0;
                $c1_views = 0;
                $c2_views = 0;
                $c3_views = 0;
                $c4_views = 0;
                $mid_views = 0;
                $pd_views = 0;
                $tib_views = 0;
                $iff_views = 0;
                $rex_views = 0;
                $cpa_views = 0;
                $lp_views = 0;
            }

            $rows[] = array_values(array_slice($tracker, 0, 40));

            $all_views += $tracker['all_coreg_views'];
            $c1_revenue += $tracker['coreg_p1_revenue'];
            $c2_revenue += $tracker['coreg_p2_revenue'];
            $c3_revenue += $tracker['coreg_p3_revenue'];
            $c4_revenue += $tracker['coreg_p4_revenue'];
            $c1_views += $tracker['coreg_p1_views'];
            $c2_views += $tracker['coreg_p2_views'];
            $c3_views += $tracker['coreg_p3_views'];
            $c4_views += $tracker['coreg_p4_views'];
            $mid_views += $tracker['all_mp_views'];
            $pd_views += $tracker['pd_views'];
            $tib_views += $tracker['tb_views'];
            $iff_views += $tracker['iff_views'];
            $rex_views += $tracker['rexadz_views'];
            $cpa_views += $tracker['cpa_views'];
            $lp_views += $tracker['lsp_views'];

            $curTrackerCode = $this->genCode($tracker);
            $rowNumber++;
        }

        //Last Row
        $rows[] = [
            '', '', '', '', '', '', '',
            '=SUM(H'.$begin.':H'.($rowNumber - 1).')', //All CLicks
            '=SUM(I'.$begin.':I'.($rowNumber - 1).')', //Survey Takers
            "=M$rowNumber/H$rowNumber", //Cost / All Clicks
            "=I$rowNumber/H$rowNumber", //Survey Takers / Clicks
            '=SUM(L'.$begin.':L'.($rowNumber - 1).')', //Total Revenue
            '=SUM(M'.$begin.':M'.($rowNumber - 1).')', // Cost
            "=(L$rowNumber - M$rowNumber) / L$rowNumber", //Margin
            "=L$rowNumber / H$rowNumber", //Revenue / All Clicks
            "=L$rowNumber / I$rowNumber", //Revenue / Survye Takers
            '=SUM(Q'.$begin.':Q'.($rowNumber - 1).')', //All Inbox
            "=Q$rowNumber / I$rowNumber", //All Inbox Survey Takers
            '=SUM(S'.$begin.':S'.($rowNumber - 1).')', //All Coreg Revenue
            "=S$rowNumber / $all_views", //All Coreg Revenue / All Views
            "=$c1_revenue / $c1_views", //Coreg P1 Revenue / Views
            "=$c2_revenue / $c2_views", //Coreg P2 Revenue / Views
            "=$c3_revenue / $c3_views", //Coreg P3 Revenue / Views
            "=$c4_revenue / $c4_views", //Coreg P4 Revenue / Views
            '=SUM(Y'.$begin.':Y'.($rowNumber - 1).')', //All Mid Paths Revenue
            "=Y$rowNumber / $mid_views", //All Mid Paths Revenue / Views
            '=SUM(AA'.$begin.':AA'.($rowNumber - 1).')', //Permission Data Revenue
            "=AA$rowNumber / $pd_views", //Permission Data Revenue / Views
            '=SUM(AC'.$begin.':AC'.($rowNumber - 1).')', //Tiburon Revenue
            "=AC$rowNumber / $tib_views", //Tiburon Revenue / Views
            '=SUM(AE'.$begin.':AE'.($rowNumber - 1).')', //Ifficient Revenue
            "=AE$rowNumber / $iff_views", //Ifficient Revenue / Views
            '=SUM(AG'.$begin.':AG'.($rowNumber - 1).')', //Rexadz Revenue
            "=AG$rowNumber / $rex_views", //Rexadz Revenue / Views
            '=SUM(AI'.$begin.':AI'.($rowNumber - 1).')', //Push Revenue
            "=AI$rowNumber / I$rowNumber", //Push Revenue / ST
            '=SUM(AK'.$begin.':AK'.($rowNumber - 1).')', //CPA Revenue
            "=AK$rowNumber / $cpa_views", //CPA Revenue / Views
            '=SUM(AM'.$begin.':AM'.($rowNumber - 1).')', //Last Page Revenue
            "=AM$rowNumber / $lp_views", //Last Page Revenue / Views
        ];

        $summaryRow[] = $rowNumber;

        $sheet->rows($rows);

        foreach ($summaryRow as $row) {
            $sheet->cells("H$row:AN$row", function ($cells) {
                $cells->setFont([
                    'bold' => true,
                ]);
                $cells->setBackground('#ffff00');
            });
        }

        // /** BOF AB TESTING **/
        // if($this->ab_testing) {
        //    // $this->createABTesting($sheet);

        //     $today = ['Total', 'Total Vs. Yesterday', '','','','',''];
        //     $thirty = ['Total', 'Total Vs. 30-Day ', '','','','',''];

        //     $colors = [
        //         'red' => '#d60707',
        //         'yellow' => '#ffc513',
        //         'green' => '#37960f'
        //     ];

        //     $today_cell = $recordCount - 2 + ($this->headerRow + 1);
        //     $thirty_cell = $recordCount - 1 + ($this->headerRow + 1);
        //     $sheet->cells("A$today_cell:B$thirty_cell", function($cells)
        //     {
        //         $cells->setFont([
        //             'bold'       =>  true
        //         ]);
        //     });

        //     $col = 'H';
        //     foreach($this->ab_columns as $column) {
        //         $today[] = $this->ab_testing['yesterday'][$column]['r'];
        //         $thirty[] = $this->ab_testing['30'][$column]['r'];

        //         $sheet->cell($col.$today_cell, function($cell) use($column, $colors) {
        //             if($this->ab_testing['yesterday'][$column]['c'] != '') $cell->setFontColor($colors[$this->ab_testing['yesterday'][$column]['c']]);
        //         });

        //         $sheet->cell($col.$thirty_cell, function($cell) use($column, $colors) {
        //             if($this->ab_testing['30'][$column]['c'] != '') $cell->setFontColor($colors[$this->ab_testing['30'][$column]['c']]);
        //         });

        //         $col++;
        //     }

        //     $sheet->row($today_cell, $today);

        //     $sheet->row($thirty_cell, $thirty);
        // }

        $recordCount = $rowNumber;
        /** BOF STEP 3 - FOOTER **/
        if ($this->footer) {
            $sheet->row($recordCount + ($this->headerRow + 1), $this->footer);

            $sheet
                ->getStyle('A'.($recordCount + ($this->headerRow + 1)).':'.'AN'.($recordCount + 2))
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
                $this->applylegendColors(($recordCount + ($this->headerRow + 1)));
            }

            $sheet->getRowDimension((2 + $recordCount))->setRowHeight(20.5);
        }
        /** EOF STEP 3 - FOOTER **/

        /** BOF STEP 4 - LEGENDS **/
        if ($this->legends) {
            // Add the legends header
            // $this->recordsCount() + $this->headerRow + 3 : The row that will be occupied by the legend header, below lists footer.
            $sheet->row($recordCount + ($this->headerRow + 3), ['Legends']);
            // Add style
            $this->legendsStyle('A'.($recordCount + ($this->headerRow + 3)).':'.$this->lastCol.($recordCount + 4));

            // Add the legends with descriptions lists
            $count = $recordCount + ($this->headerRow + 4);
            // Go through the array and insert it.
            foreach ($this->legends as $details) {
                $sheet->mergeCells('A'.$count.':B'.$count);
                $sheet->mergeCells('C'.$count.':'.end($this->columnsRange).$count);
                $sheet->row($count, $details);
                $count++;
            }
        }
        /** EOF STEP 4 - LEGENDS **/

        // /** BOF STEP 5 - OTHERS **/
        $sheet
            ->getStyle('A1:'.$this->lastCol.(1 + $recordCount + 35))
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // // ....
        // /** BOF STEP 4 - OTHERS **/
    }

    public function genCode($tracker)
    {
        // "$tracker[revenue_tracker_id]:$tracker[s1]:$tracker[s2]:$tracker[s3]:$tracker[s4]")
        // $this->filters
        $code = "$tracker[revenue_tracker_id]:";

        if (isset($this->filters['sib_s1'])) {
            $code .= "$tracker[s1]:";
        }
        if (isset($this->filters['sib_s2'])) {
            $code .= "$tracker[s2]:";
        }
        if (isset($this->filters['sib_s3'])) {
            $code .= "$tracker[s3]:";
        }
        if (isset($this->filters['sib_s4'])) {
            $code .= "$tracker[s4]";
        }

        return $code;
    }

    public function generatePerSubIDSummarySheet($sheet)
    {
        // \Log::info($this->filters);

        /** BOF STEP 1 - HEADER **/
        //Add Additional Headers
        $total_revenue_col = array_search('Total Revenue', $this->header);
        $survey_takers_col = array_search('Survey Takers', $this->header);
        if ($total_revenue_col >= 0) {
            array_splice($this->header, $total_revenue_col + 1, 0, [
                'Allocation of revenue to line below',
                'Total Revenue per Sub ID after allocation',
                'Total Revenue/Survey taker after allocation',
                'Allocated Revenue/Survey taker after allocation',
            ]);

            $this->footer = $this->header;
        }
        // \Log::info($this->header);

        // Add the header
        $sheet->row($this->headerRow, $this->header);

        //Add default header colors
        $sheet
            ->getStyle('A1:AR1')
            ->applyFromArray(
                [
                    'font' => [
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => '003300'],
                        'bold' => true,
                    ],
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'A19C9C'],
                        ],
                    ],
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F2F3F5'],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]
            );

        //Add Header Colors
        if ($total_revenue_col >= 0) {
            $legends = config('consolidatedgraph.legends');
            $aliases = [];
            foreach ($legends as $code => $data) {
                $aliases[$data['alias']] = $data;
            }

            $col = 'A';
            foreach ($this->header as $alias) {
                if (isset($aliases[$alias])) {
                    $d = $aliases[$alias];
                    $color = strtoupper(str_replace('#', '', $d['color']));
                    // \Log::info($alias .' - '. $color.' - '.$col.$this->headerRow);
                    $sheet
                        ->getStyle($col.$this->headerRow)
                        ->applyFromArray(
                            [
                                'fill' => [
                                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => ['rgb' => $color],
                                ],
                            ]
                        );
                }

                $col++;
            }
        } else {
            if ($this->applyLegendsColor) {
                $this->applylegendColors($this->headerRow);
            }
        }

        $sheet->getRowDimension('1')->setRowHeight(20.5);

        $sheet->freezePane('C2');

        // Auto size column
        foreach (range('A', 'AR') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        /** BOF STEP 2 - RECORDS/LISTS **/
        $curTrackerCode = '';
        $rows = [];
        $rowNumber = 2;
        $begin = 2;
        $date_used = $this->getSnapShotPeriodRange();
        $recordCount = count($this->subIDSummaryRecords);
        $alphabet = range('A', 'Z');
        $totalRevAlpha = $total_revenue_col >= 0 ? $alphabet[$total_revenue_col] : 'L';
        $surveyTakerAlpha = $survey_takers_col >= 0 ? $alphabet[$survey_takers_col] : 'I';
        $allocRevAlpha = $total_revenue_col >= 0 ? $alphabet[$total_revenue_col + 1] : 'M';
        $totalRevAllocAlpha = $total_revenue_col >= 0 ? $alphabet[$total_revenue_col + 2] : 'N';

        $byRevTrackers = [];
        foreach ($this->subIDSummaryRecords as $data) {
            if (! isset($byRevTrackers[$data['revenue_tracker_id']])) {
                $byRevTrackers[$data['revenue_tracker_id']] = [
                    'count' => 0,
                    'hasToAllocate' => false,
                    'data' => [],
                    'allocate_row' => 0,
                ];
            }

            if ($data['s1'] == '' && $data['s2'] == '' && $data['s3'] == '' && $data['s4'] == '') {
                $byRevTrackers[$data['revenue_tracker_id']]['hasToAllocate'] = true;
                $byRevTrackers[$data['revenue_tracker_id']]['allocate_row'] = $byRevTrackers[$data['revenue_tracker_id']]['count'];
            }

            $byRevTrackers[$data['revenue_tracker_id']]['count'] += 1;
            $byRevTrackers[$data['revenue_tracker_id']]['data'][] = $data;
        }
        // \Log::info($byRevTrackers);
        $start_data_row = $rowNumber;
        $end_data_row = $rowNumber;
        foreach ($byRevTrackers as $revenue_tracker => $data) { //rev tracker
            $hasToAllocate = $data['hasToAllocate'] && $data['count'] > 1;
            $the_allocate_row = $data['allocate_row'] + $rowNumber;
            $rc = 0;
            $totalRevenueFormula = $totalRevAlpha.$the_allocate_row;
            $start = $rowNumber + 1;
            $end = $rowNumber + $data['count'] - 1;
            foreach ($data['data'] as $drow) {
                $row = [];
                //Check if to allocate
                foreach ($drow as $code => $value) {
                    $row[] = $value;
                    if ($code == 'source_revenue') {
                        if ($hasToAllocate) {
                            if ($data['allocate_row'] == $rc) {
                                $row[] = '';
                                $row[] = '';
                                $row[] = '';
                                $row[] = '';
                            } else {
                                $row[] = "=$totalRevenueFormula*(L$rowNumber/SUM(L$start:L$end))";
                                $row[] = "=L$rowNumber+M$rowNumber";
                                $row[] = "=N$rowNumber/I$rowNumber";
                                $row[] = "=M$rowNumber/I$rowNumber";
                            }
                        } else {
                            $row[] = '';
                            $row[] = '';
                            $row[] = '';
                            $row[] = '';
                        }
                    }
                    // \Log::info($code.' - '.$value);
                }
                $rows[] = $row;
                $rc++;
                $rowNumber++;
            }
        }
        $sheet->rows($rows);
        $end_data_row = $rowNumber - 1;

        // Add style
        $sheet
            ->getStyle('A2:AC'.(1 + $recordCount))
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

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
        $last_col = $sheet->getHighestDataColumn();
        $cur_row = $this->headerRow + 1;
        foreach ($this->subIDSummaryRecords as $record) {
            if ($cur_rev_tracker != null && $record['revenue_tracker_id'] != $cur_rev_tracker) {
                $sheet->getStyle('A'.$cur_row.':'.$last_col.$cur_row)->applyFromArray($styleArray);
            }
            $cur_rev_tracker = $record['revenue_tracker_id'];

            $sheet->getRowDimension($i + 1)->setRowHeight(20);
            $i++;
            $cur_row++;
        }

        /** BOF STEP 3 - FOOTER **/
        if ($this->footer) {
            $sheet->row($recordCount + ($this->headerRow + 1), $this->footer);

            $sheet
                ->getStyle('A'.($recordCount + ($this->headerRow + 1)).':'.'AR'.($recordCount + 2))
                ->applyFromArray(
                    [
                        'font' => [
                            'size' => 11,
                            'name' => 'Calibri',
                            'color' => ['rgb' => '003300'],
                            'bold' => true,
                        ],
                        'borders' => [
                            'top' => [
                                'style' => \PHPExcel_Style_Border::BORDER_DOUBLE,
                                'color' => ['argb' => 'A19C9C'],
                            ],
                        ],
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'F2F3F5'],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]
                );

            if ($total_revenue_col >= 0) {
                $col = 'A';
                foreach ($this->header as $alias) {
                    if (isset($aliases[$alias])) {
                        $d = $aliases[$alias];
                        $color = strtoupper(str_replace('#', '', $d['color']));
                        // \Log::info($alias .' - '. $color.' - '.$col.$this->headerRow);
                        $sheet
                            ->getStyle($col.($recordCount + $this->headerRow + 1))
                            ->applyFromArray(
                                [
                                    'fill' => [
                                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => ['rgb' => $color],
                                    ],
                                ]
                            );
                    }

                    $col++;
                }
            } else {
                if ($this->applyLegendsColor) {
                    $this->applylegendColors(($recordCount + ($this->headerRow + 1)));
                }
            }

            $sheet->getRowDimension((2 + $recordCount))->setRowHeight(20.5);
        }
        /** EOF STEP 3 - FOOTER **/

        // Add Total & Currency
        if ($total_revenue_col >= 0) {
            $rows = [];
            $rows[] = [];
            $rowNumber++;
            $rows[] = [];
            $rowNumber++;
            $col = 'A';
            $tRow = [];
            $fRow = [];
            $rowNumber++;
            foreach ($this->header as $alias) {
                // \Log::info($alias);
                if (in_array($alias, ['Total Revenue', 'Allocation of revenue to line below', 'Total Revenue per Sub ID after allocation', 'Total Revenue/Survey taker after allocation', 'Allocated Revenue/Survey taker after allocation'])) {
                    // \Log::info("SUM($col$start_data_row:$col$end_data_row)");
                    $tRow[] = "=SUM($col$start_data_row:$col$end_data_row)";
                    $fRow["$col$start_data_row:$col$end_data_row"] = '_("$"* #,##0.00_);_("$"* (#,##0.00);_("$"* "-"??_);_(@_)';
                    $fRow["$col$rowNumber:$col$rowNumber"] = '_("$"* #,##0.00_);_("$"* (#,##0.00);_("$"* "-"??_);_(@_)';
                } else {
                    $tRow[] = '';
                }
                $col++;
            }
            $rows[] = $tRow;
            $sheet->rows($rows);
            $sheet->cells("A$rowNumber:AR$rowNumber", function ($cells) {
                $cells->setFont([
                    'bold' => true,
                ]);
            });
            $sheet->setColumnFormat($fRow);
        }

        // /** BOF STEP 5 - OTHERS **/
        $sheet
            ->getStyle('A1:'.$this->lastCol.(1 + $recordCount + 35))
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

    }

    public function getSnapShotPeriodRange()
    {
        $date = '';
        $date_from = $this->filters['date_from'] != '' ? Carbon::parse($this->filters['date_from'])->toDateString() : '';
        $date_to = $this->filters['date_to'] != '' ? Carbon::parse($this->filters['date_to'])->toDateString() : '';
        $date = $date_from.' to '.$date_to;

        if ($this->filters['predefine_dates'] != '') {
            switch ($this->filters['predefine_dates']) {
                case 'yesterday':
                    // $date_from = Carbon::yesterday()->startOfDay();
                    // $date_to = Carbon::yesterday()->endOfDay();
                    $date = Carbon::yesterday()->toDateString();
                    break;
                case 'week_to_date':
                    $date_from = Carbon::now()->startOfWeek();
                    $date_to = Carbon::now()->endOfDay();
                    $date = $date_from.' to '.$date_to;
                    break;
                case 'month_to_date':
                    $date_from = Carbon::now()->startOfMonth();
                    $date_to = Carbon::now()->endOfDay();
                    $date = $date_from.' to '.$date_to;
                    break;
                case 'last_month':
                    $date_from = Carbon::now()->subMonth()->startOfMonth();
                    $date_to = Carbon::now()->subMonth()->endOfMonth();
                    $date = $date_from.' to '.$date_to;
                    break;
            }
        }

        return $date;
    }
}
