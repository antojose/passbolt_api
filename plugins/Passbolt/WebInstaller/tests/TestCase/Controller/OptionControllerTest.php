<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.5.0
 */
namespace Passbolt\WebInstaller\Test\TestCase\Controller;

use App\Utility\Healthchecks;
use Cake\Core\Configure;
use Passbolt\WebInstaller\Controller\WebInstallerController;
use Passbolt\WebInstaller\Test\Lib\WebInstallerIntegrationTestCase;

class OptionsControllerTest extends WebInstallerIntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mockPassboltIsNotconfigured();
        $this->initWebInstallerSession();
    }

    public function testViewSuccess()
    {
        $this->get('/install/options');
        $data = ($this->_getBodyAsString());
        $this->assertResponseOk();
        $this->assertContains('Options', $data);
    }

    public function testPostSuccess()
    {
        $postData = [
            'full_base_url' => 'http://passbolt.dev/',
            'public_registration' => 0,
            'force_ssl' => 0
        ];
        $this->post('/install/options', $postData);
        $this->assertResponseCode(302);
        $this->assertRedirectContains('install/account_creation');

        $expectedSessionSettings = $postData;
        // The full base url last / should be trimed.
        $expectedSessionSettings['full_base_url'] = 'http://passbolt.dev';
        $this->assertSession($expectedSessionSettings, 'webinstaller.options');
    }

    public function testPostSuccess_AdminAlreadyExists()
    {
        $this->session(['webinstaller' => ['initialized' => true, 'hasAdmin' => true]]);
        $postData = [
            'full_base_url' => 'http://passbolt.dev',
            'public_registration' => 0,
            'force_ssl' => 0
        ];
        $this->post('/install/options', $postData);
        $this->assertResponseCode(302);
        $this->assertRedirectContains('install/installation');
    }

    public function testPostError_InvalidData()
    {
        $postData = [
            'full_base_url' => 'http://passbolt.dev',
            'public_registration' => 'invalid-data',
            'force_ssl' => 0
        ];
        $this->post('/install/options', $postData);
        $data = ($this->_getBodyAsString());
        $this->assertResponseOk();
        $this->assertContains('The data entered are not correct', $data);
        $this->assertSession(null, 'webinstaller.options');
    }
}