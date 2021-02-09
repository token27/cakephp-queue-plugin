<?php
declare(strict_types=1);

namespace Queue\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Queue\Model\Table\QueueLogsTable;

/**
 * Queue\Model\Table\QueueLogsTable Test Case
 */
class QueueLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Queue\Model\Table\QueueLogsTable
     */
    protected $QueueLogs;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Queue.QueueLogs',
        'plugin.Queue.QueueJobs',
        'plugin.Queue.QueueWorkers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('QueueLogs') ? [] : ['className' => QueueLogsTable::class];
        $this->QueueLogs = $this->getTableLocator()->get('QueueLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->QueueLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
