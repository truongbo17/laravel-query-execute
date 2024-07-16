<?php

namespace Bo\LaravelQueryExecute\Http\Controllers;

use Bo\Base\Http\Controllers\CrudController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

class QueryExecuteController extends CrudController
{
    public const DEFAULT_LIMIT_QUERIES = 1000;

    public function home(Request $request): View
    {
        return view('laravel-query-execute::home');
    }

    public function query(Request $request): View
    {
        $connections = config('laravel-query-execute.query-execute.connections');
        return view('laravel-query-execute::query', [
            'connections' => $connections,
        ]);
    }

    public function version(Request $request): View
    {
        return view('laravel-query-execute::version');
    }

    public function getTables(Request $request): array
    {
        try {
            $connection = $request->input('connection');
            if (empty($connection)) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => trans('laravel-query-execute::query-execute.invalid-input'),
                ];
            }

            $tables = DB::connection($connection)->select('SHOW TABLES');
            $dbName = 'Tables_in_' . DB::getDatabaseName();

            $result = [];
            foreach ($tables as $table) {
                $tableName = $table->$dbName;
                $columns = Schema::getColumnListing($tableName);
                foreach ($columns as $column) {
                    $result[$tableName][] = $column;
                }
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => trans('laravel-query-execute::query-execute.success'),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'data' => [],
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function execute(Request $request): array
    {
        $connection = $request->input('connection');
        $query = $request->input('query');
        $limit = $request->input('limit', self::DEFAULT_LIMIT_QUERIES);

        try {
            $db = DB::connection($connection);
            if (empty($connection) || empty($query)) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => trans('laravel-query-execute::query-execute.invalid-input'),
                ];
            }
            $result = [];

            $db->enableQueryLog();
            $db->beginTransaction();

            $queries = $this->splitSqlCommands($query);

            $pdo = $db->getPdo();

            foreach ($queries as $queryRun) {
                if (strtoupper($queryRun['type']) === 'SELECT') {
                    $stmt = $pdo->prepare($queryRun['query']);
                    $stmt->execute();

                    $columnNames = [];
                    $columnAliasQueries = [];
                    for ($i = 0; $i < $stmt->columnCount(); $i++) {
                        $meta = $stmt->getColumnMeta($i);
                        $tableName = $meta['table'] ?? '';
                        $columnName = $meta['name'] ?? '';
                        $columnNames[] = $tableName . '_' . $columnName;
                        $columnAliasQueries[] = $tableName . '.' . $columnName . ' as ' . $tableName . '_' . $columnName;
                    }

                    $pattern = "/SELECT\s.*\sFROM/i";
                    $newQuery = preg_replace($pattern, 'SELECT ' . implode(', ', $columnAliasQueries) . ' FROM', $queryRun['query']);
                    $resultLast = $db->select($newQuery);

                    $last['result'] = [
                        'columns' => $columnNames,
                        'data' => $resultLast,
                    ];

                    $logs = $db->getQueryLog();
                    $lastLog = end($logs);
                    $last['query'] = (string)$lastLog['query'];
                    $last['time'] = $lastLog['time'] . 's';
                    $last['table'] = $this->extractTableName($lastLog['query']);

                    $result[] = $last;
                }
            }

            $db->commit();

            return [
                'success' => true,
                'data' => $result,
                'message' => trans('laravel-query-execute::query-execute.success'),
            ];
        } catch (\Throwable $exception) {
            DB::connection($connection)->rollBack();
            report($exception);

            return [
                'success' => false,
                'data' => [],
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function splitSqlCommands($sqlCommands): array
    {
        $keywords = [
            'SHOW CREATE',
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'CREATE',
            'ALTER',
            'DROP',
            'TRUNCATE',
            'GRANT',
            'EXPLAIN',
            'SHOW',
            'REPLACE',
            'RENAME',
            'CREATE INDEX',
            'CREATE TABLE',
            'REVOKE',
            'COMMIT',
            'ROLLBACK',
            'SAVEPOINT',
            'LOCK',
            'UNLOCK',
            'ANALYZE',
            'DESCRIBE',
            'USE',
        ];
        $pattern = '/\b(' . implode('|', $keywords) . ')\b/i';
        preg_match_all($pattern, $sqlCommands, $matches, PREG_OFFSET_CAPTURE);

        $commands = [];
        foreach ($matches[0] as $key => $match) {
            $commandType = strtoupper($match[0]);
            $startPos = $match[1];
            $endPos = isset($matches[0][$key + 1]) ? $matches[0][$key + 1][1] : strlen($sqlCommands);
            $command = trim(substr($sqlCommands, $startPos, $endPos - $startPos));
            $commands[] = ['type' => $commandType, 'query' => $command];
        }

        return $commands;
    }

    public function extractTableName($sqlCommand): string
    {
        $pattern = '/\bFROM\b\s+(\w+)/i';
        preg_match($pattern, $sqlCommand, $matches);
        return $matches[1] ?? 'result';
    }
}
