<?php
declare(strict_types=1);

namespace Queue\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Queue\Model\Table\QueueJobsTable;

/**
 * Queue\Model\Table\QueueJobsTable Test Case
 */
class QueueJobsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Queue\Model\Table\QueueJobsTable
     */
    protected $QueueJobs;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Queue.QueueJobs',
        'plugin.Queue.Users',
        'plugin.Queue.QueueWorkers',
        'plugin.Queue.QueueLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('QueueJobs') ? [] : ['className' => QueueJobsTable::class];
        $this->QueueJobs = $this->getTableLocator()->get('QueueJobs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->QueueJobs);

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
