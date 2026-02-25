<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\HistoricalTide;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel; // We are not using this facade, but IOFactory directly

class TideImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_import_button()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Import Data Excel');
    }

    public function test_user_can_import_tide_data()
    {
        $user = User::factory()->create();

        // Create a mock Excel file
        // Since we can't easily create a real Excel file in memory without saving to disk,
        // and PhpSpreadsheet IOFactory loads from file path.
        // We will create a temporary CSV/xlsx file.
        
        $fileName = 'tide_data.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Time');
        $sheet->setCellValue('C1', 'Height');
        
        // Data
        $sheet->setCellValue('A2', '2024-01-01');
        $sheet->setCellValue('B2', '06:00:00');
        $sheet->setCellValue('C2', 1.5);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $path = sys_get_temp_dir() . '/' . $fileName;
        $writer->save($path);
        
        $file = new UploadedFile($path, $fileName, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user)->post('/dashboard/import', [
            'file' => $file,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        // Verify database

        $this->assertDatabaseHas('historical_tides', [
            'date' => '2024-01-01 00:00:00',
            'height' => 1.5,
        ]);
        
        unlink($path);
    }
}
