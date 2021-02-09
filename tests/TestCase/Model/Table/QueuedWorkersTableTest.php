<?php
declare(strict_types=1);

namespace Queue\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Queue\Model\Table\QueueWorkersTable;

/**
 * Queue\Model\Table\QueueWorkersTable Test Case
 */
class QueueWorkersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Queue\Model\Table\QueueWorkersTable
     */
    protected $QueueWorkers;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Queue.QueueWorkers',
        'plugin.Queue.QueueLogs',
        'plugin.Queue.QueueJobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('QueueWorkers') ? [] : ['className' => QueueWorkersTable::class];
        $this->QueueWorkers = $this->getTableLocator()->get('QueueWorkers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->QueueWorkers);

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
