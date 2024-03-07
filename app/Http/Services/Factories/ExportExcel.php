<?php

namespace App\Http\Services\Factories;

use Maatwebsite\Excel\Classes\PHPExcel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

abstract class ExportExcel extends \Maatwebsite\Excel\Excel
{
    /**
     * Excel object
     *
     * @var PHPExcel
     */
    protected $excel;

    /**
     * Excel object
     *
     * @var LaravelExcelWriter
     */
    protected $createdFile;

    /**
     * Default
     *
     * @var string
     */
    protected $fileName = 'FileName';

    protected $title = 'Title';

    protected $creator = 'Creator';

    protected $company = 'Company';

    protected $description = 'Description';

    protected $sheetName = 'SheetName';

    protected $filters;

    /**
     * Abstract functions.
     */
    abstract public function generateSheet($sheet);

    abstract public function generatePerSubIDSheet($sheet);

    abstract public function generatePerSubIDSummarySheet($sheet);

    /**
     * Set the file name.
     *
     * @param  string  $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Set the file title.
     *
     * @param  string  $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the creator of file.
     *
     * @param  string  $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Set the Company.
     *
     * @param  string  $company
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Set the file description.
     *
     * @param  string  $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the sheet name.
     *
     * @param  string  $sheetName
     */
    public function setSheetName($sheetName)
    {
        $this->sheetName = $sheetName;

        return $this;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Generate/Create the excel file.
     *
     * @var LaravelExcelWriter;
     */
    public function generate()
    {

        $this->createdFile = $this->store(function ($excel) {
            $this->excel = $excel;

            // Set the title
            $excel->setTitle($this->title);
            // Chain the setters
            $excel->setCreator($this->creator)
                ->setCompany($this->company);
            // Call them separately
            $excel->setDescription($this->description);
            // Create Sheets
            $excel->sheet($this->sheetName, function ($sheet) {
                // Abstract function to be supllied by the extending class.
                $this->generateSheet($sheet);
            });

            $excel->sheet('Totals Per SubID', function ($sheet) {
                // Abstract function to be supllied by the extending class.
                $this->generatePerSubIDSheet($sheet);
            });

            $excel->sheet('Totals Per SubID Summary', function ($sheet) {
                // Abstract function to be supllied by the extending class.
                $this->generatePerSubIDSummarySheet($sheet);
            });

        }, $this->fileName, 'downloads');

    }

    /**
     * Export the created file.
     *
     * @param  'string'  $type  type of file, range: xls, xlsx, pdf, csv ... etc.
     */
    public function export($type)
    {
        if (! $this->createdFile instanceof \Maatwebsite\Excel\Writers\LaravelExcelWriter) {
            return;
        }

        $this->createdFile->export($type);
    }
}
