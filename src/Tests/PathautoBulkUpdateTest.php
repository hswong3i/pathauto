<?php

/**
 * @file
 * Contains \Drupal\pathauto\Tests\PathautoBulkUpdateTest.
 */

namespace Drupal\pathauto\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Bulk update functionality tests.
 *
 * @group pathauto
 */
class PathautoBulkUpdateTest extends WebTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'pathauto');

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The created nodes.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $nodes;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Allow other modules to add additional permissions for the admin user.
    $permissions = array(
      'administer pathauto',
      'administer url aliases',
      'create url aliases',
    );
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);
  }


  public function testBulkUpdate() {
    // Create some nodes.
    $this->nodes = array();
    for ($i = 1; $i <= 5; $i++) {
      $node = $this->drupalCreateNode();
      $this->nodes[$node->id()] = $node;
    }

    // Clear out all aliases.
    $this->deleteAllAliases();

    // Bulk create aliases.
    $edit = array(
      'update[node_pathauto_bulk_update_batch_process]' => TRUE,
      'update[user_pathauto_bulk_update_batch_process]' => TRUE,
    );
    $this->drupalPostForm('admin/config/search/path/update_bulk', $edit, t('Update'));
    // 5 nodes + 2 users.
    $this->assertText('Generated 7 URL aliases.');

    // Check that aliases have actually been created.
    foreach ($this->nodes as $node) {
      $this->assertEntityAliasExists($node);
    }
    $this->assertEntityAliasExists($this->adminUser);

    // Add a new node.
    $new_node = $this->drupalCreateNode(array('path' => array('alias' => '', 'pathauto' => FALSE)));

    // Run the update again which should only run against the new node.
    $this->drupalPostForm('admin/config/search/path/update_bulk', $edit, t('Update'));
    // 1 node + 0 users.
    $this->assertText('Generated 1 URL alias.');

    $this->assertEntityAliasExists($new_node);
  }

}
