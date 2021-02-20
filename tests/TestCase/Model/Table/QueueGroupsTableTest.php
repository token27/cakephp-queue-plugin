<?php
declare(strict_types=1);

namespace Queue\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Queue\Model\Table\QueueGroupsTable;

/**
 * Queue\Model\Table\QueueGroupsTable Test Case
 */
class QueueGroupsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Queue\Model\Table\QueueGroupsTable
     */
    protected $QueueGroups;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Queue.QueueGroups',
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
        $config = $this->getTableLocator()->exists('QueueGroups') ? [] : ['className' => QueueGroupsTable::class];
        $this->QueueGroups = $this->getTableLocator()->get('QueueGroups', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->QueueGroups);

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
