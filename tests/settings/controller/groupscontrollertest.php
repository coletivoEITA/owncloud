<?php
/**
 * @author Lukas Reschke
 * @copyright 2014 Lukas Reschke lukas@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OC\Settings\Controller;

use \OC\Settings\Application;
use OCP\AppFramework\Http\DataResponse;

/**
 * @package OC\Settings\Controller
 */
class GroupsControllerTest extends \Test\TestCase {

	/** @var \OCP\AppFramework\IAppContainer */
	private $container;

	/** @var GroupsController */
	private $groupsController;

	protected function setUp() {
		$app = new Application();
		$this->container = $app->getContainer();
		$this->container['AppName'] = 'settings';
		$this->container['GroupManager'] = $this->getMockBuilder('\OCP\IGroupManager')
			->disableOriginalConstructor()->getMock();
		$this->container['UserSession'] = $this->getMockBuilder('\OC\User\Session')
			->disableOriginalConstructor()->getMock();
		$this->container['L10N'] = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()->getMock();
		$this->container['L10N']->expects($this->any())
					->method('t')
					->will($this->returnCallback(function($text, $parameters = array()) {
							return vsprintf($text, $parameters);
					}));
		$this->groupsController = $this->container['GroupsController'];

	}

	public function testCreateWithExistingGroup() {
		$this->container['GroupManager']
			->expects($this->once())
			->method('groupExists')
			->with('ExistingGroup')
			->will($this->returnValue(true));

		$expectedResponse = new DataResponse(array('status' => 'error', 'data' => array('message' => 'Group already exists.')));
		$response = $this->groupsController->create('ExistingGroup');
		$this->assertEquals($expectedResponse, $response);
	}

	public function testCreateSuccessful() {
		$this->container['GroupManager']
			->expects($this->once())
			->method('groupExists')
			->with('NewGroup')
			->will($this->returnValue(false));
		$this->container['GroupManager']
			->expects($this->once())
			->method('createGroup')
			->with('NewGroup')
			->will($this->returnValue(true));

		$expectedResponse = new DataResponse(array('status' => 'success', 'data' => array('groupname' => 'NewGroup')));
		$response = $this->groupsController->create('NewGroup');
		$this->assertEquals($expectedResponse, $response);
	}

	public function testCreateUnsuccessful() {
		$this->container['GroupManager']
			->expects($this->once())
			->method('groupExists')
			->with('NewGroup')
			->will($this->returnValue(false));
		$this->container['GroupManager']
			->expects($this->once())
			->method('createGroup')
			->with('NewGroup')
			->will($this->returnValue(false));

		$expectedResponse = new DataResponse(array('status' => 'error', 'data' => array('message' => 'Unable to add group.')));
		$response = $this->groupsController->create('NewGroup');
		$this->assertEquals($expectedResponse, $response);
	}

	public function testDestroySuccessful() {
		$group = $this->getMockBuilder('\OC\Group\Group')
			->disableOriginalConstructor()->getMock();
		$this->container['GroupManager']
			->expects($this->once())
			->method('get')
			->with('ExistingGroup')
			->will($this->returnValue($group));
		$group
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(true));

		$expectedResponse = new DataResponse(array('status' => 'success', 'data' => array('groupname' => 'ExistingGroup')));
		$response = $this->groupsController->destroy('ExistingGroup');
		$this->assertEquals($expectedResponse, $response);
	}

	public function testDestroyUnsuccessful() {
		$this->container['GroupManager']
			->expects($this->once())
			->method('get')
			->with('ExistingGroup')
			->will($this->returnValue(null));

		$expectedResponse = new DataResponse(array('status' => 'error', 'data' => array('message' => 'Unable to delete group.')));
		$response = $this->groupsController->destroy('ExistingGroup');
		$this->assertEquals($expectedResponse, $response);
	}

}
