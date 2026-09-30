<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResearchResultsTest extends TestCase
{
    public function test_dashboard_distinguishes_mapping_metrics_from_selected_model_metrics(): void
    {
        $response = $this->get('/');

        $response->assertSeeTextInOrder([
            'Model pembuat peta',
            'XGBoost V4.1 STRICT FINAL',
            '81,77%',
            'Model pembanding SELECTED',
            'EXTENDED',
            '74,14%',
            'Luas kandidat setelah filter DOA',
            '26.047,32 ha',
        ]);
        $response->assertSeeText('membuka halaman tidak menjalankan pelatihan ulang');
        $response->assertDontSeeText('histori pola cuaca');
    }

    public function test_research_summary_shows_unavailable_data_without_inventing_metrics(): void
    {
        $view = $this->view('partials.research-results', [
            'researchResults' => [
                'mapping' => [],
                'selected' => [],
                'repeated' => [],
                'doa' => [],
            ],
        ]);

        $view->assertSee('Data belum tersedia');
        $view->assertDontSee('81,77%');
        $view->assertDontSee('74,14%');
        $view->assertDontSee('26.047,32 ha');
    }
}
