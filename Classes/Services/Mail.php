<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * An frontend user controller
 */
class Tx_SfRegister_Services_Mail implements t3lib_Singleton {
	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var array
	 */
	protected $frameworkConfiguration = array();

	/**
	 * @var Tx_Extbase_Object_ManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param array $settings
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;

		return $this;
	}
	
	/**
	 * @param Tx_Extbase_Object_ManagerInterface $objectManager
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectObjectManager($objectManager) {
		$this->objectManager = $objectManager;

		return $this;
	}


	/**
	 * Send an email on registration request to activate the user by admin
	 * 
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 */
	public function sendAdminActivationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$user->setMailhash(md5($user->getUsername() . time() . $user->getEmail()));
// @todo fix me
		$subject = Tx_Extbase_Utility_Localization::translate('emails.confirmationSubject', 'sf_register');
		$subject = vsprintf($subject, array('sitename'));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('AdminActivationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$user->getEmail(),
			'adminEmail',
			$subject,
			$message
		);

		return $user;
	}

	/**
	 * Send an email on registration request to activate the user by user
	 * 
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 */
	public function sendUserActivationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$user->setMailhash(md5($user->getUsername() . time() . $user->getEmail()));
// @todo fix me
		$subject = Tx_Extbase_Utility_Localization::translate('emails.confirmationSubject', 'sf_register');
		$subject = vsprintf($subject, array('sitename'));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('UserActivationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$user->getEmail(),
			'userEmail',
			$subject,
			$message
		);

		return $user;
	}

	/**
	 * Send an email notify about the registration to the admin
	 * 
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 */
	public function sendAdminNotificationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
// @todo fix me
		$subject = Tx_Extbase_Utility_Localization::translate('emails.confirmationSubject', 'sf_register');
		$subject = vsprintf($subject, array('sitename'));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('AdminNotificationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$user->getEmail(),
			'adminEmail',
			$subject,
			$message
		);

		return $user;
	}

	/**
	 * Send an email notify about the registration to the user
	 * 
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 */
	public function sendUserNotificationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
// @todo fix me
		$subject = Tx_Extbase_Utility_Localization::translate('emails.confirmationSubject', 'sf_register');
		$subject = vsprintf($subject, array('sitename'));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('UserNotificationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$user->getEmail(),
			'userEmail',
			$subject,
			$message
		);

		return $user;
	}


	/**
	 * @param string $recipient
	 * @param string $typeOfEmail
	 * @param string $subject
	 * @param string $message
	 * @return boolean
	 */
	protected function sendEmail($recipient, $typeOfEmail, $subject, $message) {
			// TODO replace by t3lib_mail_Mailer as t3lib_htmlmail is deprecated
		$htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$htmlMail->start();
		$htmlMail->recipient = $recipient;
		$htmlMail->subject = $subject;
		$htmlMail->from_email = $this->settings[$typeOfEmail]['fromEmail'];
		$htmlMail->from_name = $this->settings[$typeOfEmail]['fromName'];
		$htmlMail->returnPath = $this->settings[$typeOfEmail]['fromEmail'];
		$htmlMail->addPlain($message);
		$htmlMail->setHTML($htmlMail->encodeMsg($message));

		return $htmlMail->send($recipient);
	}

	/**
	 * @param string $templateName
	 * @return string
	 */
	protected function getTemplatePathAndFilename($templateName) {
		$this->getFrameworkConfiguration();

		$templateRootPath = $this->getAbsoluteTemplateRootPath();
		$templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.html';

		return $templatePathAndFilename;
	}

	/**
	 * @return array
	 */
	protected function getFrameworkConfiguration() {
		$this->frameworkConfiguration = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();

		return $this->frameworkConfiguration;
	}

	/**
	 * @return string
	 */
	protected function getAbsoluteTemplateRootPath() {
		$templateRootPath = '';

		if ($this->frameworkConfiguration['view']['templateRootPath'] === '') {
			$templateRootPath = t3lib_extMgm::extPath('sf_register') . 'Resources/Private/Templates/';
		} else {
			$templateRootPath = $this->frameworkConfiguration['view']['templateRootPath'];
		}

		return t3lib_div::getFileAbsFileName($templateRootPath);
	}

	/**
	 * renders the given Template file via fluid rendering engine.
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $templateFile		absolute path to the template File
	 * @param array $vars an array of all variables you want to assgin to the view f.e: array('blog'=> $blog, 'posts' => $posts)
	 * @return string of the rendered View.
	 */
	protected function renderFileTemplate($controller, $action, $templateFile, array $vars) {
		$templateParser = Tx_Fluid_Compatibility_TemplateParserBuilder::build();
		$objectManager = t3lib_div::makeInstance('Tx_Fluid_Compatibility_ObjectManager');

		$data = '';
		$templateContent = file_get_contents($templateFile);
		if ($templateContent !== false) {$content = $templateParser->parse($templateContent);
			$variableContainer = $objectManager->create('Tx_Fluid_Core_ViewHelper_TemplateVariableContainer', $vars);
			$viewHelperVariableContainer = $objectManager->create('Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer');

			$controllerContext = $objectManager->create('Tx_Extbase_MVC_Controller_ControllerContext');
			$request = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Request');
			$request->setPluginName($this->frameworkConfiguration['pluginName']);
			$request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
			$request->setControllerName($controller);
			$request->setControllerActionName($action);
			$request->setRequestURI(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
			$request->setBaseURI(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
			$request->setMethod((isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : NULL);

			$uriBuilder = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Routing_UriBuilder');
			$uriBuilder->setRequest($request);

			$controllerContext->setRequest($request);
			$controllerContext->setUriBuilder($uriBuilder);

			$renderingContext = $objectManager->create('Tx_Fluid_Core_Rendering_RenderingContext');
			$renderingContext->setTemplateVariableContainer($variableContainer);
			$renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
			$renderingContext->setControllerContext($controllerContext);

			$data = $content->render($renderingContext);
		}

		return $data;
	}
}

?>