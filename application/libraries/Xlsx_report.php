<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Streams a multi-sheet .xlsx download built from plain arrays, via
 * PhpSpreadsheet (composer). One workbook per call; each entry in $sheets is
 * array('title' => string, 'headers' => array<string>, 'rows' => array<array>)
 * with each row's values in the same order as $headers.
 */
class Xlsx_report
{
    public function download($filename, array $sheets)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (array_values($sheets) as $i => $sheet) {
            $worksheet = $spreadsheet->createSheet($i);
            $worksheet->setTitle($this->_safe_sheet_title($sheet['title'], $i));

            $column_count = count($sheet['headers']);
            foreach ($sheet['headers'] as $col => $header) {
                $worksheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }
            if ($column_count > 0) {
                $last_column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_count);
                $worksheet->getStyle('A1:' . $last_column . '1')->getFont()->setBold(true);
            }

            $row_num = 2;
            foreach ($sheet['rows'] as $row) {
                $col = 1;
                foreach ($row as $value) {
                    $worksheet->setCellValueByColumnAndRow($col, $row_num, $value);
                    $col++;
                }
                $row_num++;
            }

            for ($col = 1; $col <= $column_count; $col++) {
                $worksheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        if (empty($sheets)) {
            $spreadsheet->createSheet(0);
        }
        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /** Excel sheet titles: max 31 chars, no \ / ? * [ ] : characters. */
    private function _safe_sheet_title($title, $index)
    {
        $title = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', (string) $title);
        $title = trim($title);

        return $title !== '' ? substr($title, 0, 31) : ('Sheet' . ($index + 1));
    }
}
