<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Database\QueryException;
use PDOException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_500_page_shows_database_error_for_query_exceptions(): void
    {
        $html = view('errors.500', [
            'exception' => new QueryException('sqlite', 'select 1', [], new Exception('SQLSTATE[HY000]')),
        ])->render();

        $this->assertStringContainsString('Database error', $html);
    }

    public function test_500_page_shows_database_error_for_pdo_exceptions(): void
    {
        $html = view('errors.500', [
            'exception' => new PDOException('SQLSTATE[HY000] [2002] Connection refused'),
        ])->render();

        $this->assertStringContainsString('Database error', $html);
    }

    public function test_500_page_shows_generic_error_otherwise(): void
    {
        $html = view('errors.500', ['exception' => new Exception('boom')])->render();

        $this->assertStringContainsString('Something went wrong', $html);
        $this->assertStringNotContainsString('Database error', $html);
    }
}
