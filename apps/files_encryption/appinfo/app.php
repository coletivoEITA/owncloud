<?php

\OCP\Util::addTranslations('files_encryption');
\OCP\Util::addscript('files_encryption', 'encryption');
\OCP\Util::addscript('files_encryption', 'detect-migration');

if (!OC_Config::getValue('maintenance', false)) {
	OC_FileProxy::register(new OCA\Encryption\Proxy());

	// User related hooks
	OCA\Encryption\Helper::registerUserHooks();

	// Sharing related hooks
	OCA\Encryption\Helper::registerShareHooks();

	// Filesystem related hooks
	OCA\Encryption\Helper::registerFilesystemHooks();

	// App manager related hooks
	OCA\Encryption\Helper::registerAppHooks();

	if(!in_array('crypt', stream_get_wrappers())) {
		stream_wrapper_register('crypt', 'OCA\Encryption\Stream');
	}
} else {
	// logout user if we are in maintenance to force re-login
	OCP\User::logout();
}

// Register settings scripts
OCP\App::registerAdmin('files_encryption', 'settings-admin');
OCP\App::registerPersonal('files_encryption', 'settings-personal');
