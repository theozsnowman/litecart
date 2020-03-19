<?php
	$_GET['vmod'] = basename($_GET['vmod']);

	try {
		if (empty($_GET['vmod'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMod'));

		$file = FS_DIR_APP . 'vmods/' . basename($_GET['vmod']);

		if (!is_file($file)) throw new Exception(language::translate('error_file_could_not_be_found', 'The file could not be found'));

		$directives = [];

		$xml = simplexml_load_file($file);

		if (empty($xml->setting)) {
			throw new Exception(language::translate('error_nothing_to_configure', 'Nothing to configure'));
		}

		if ($json = @json_decode(file_get_contents(FS_DIR_APP . 'vmods/' . '.settings'), true)) {
			$settings = $json;
		} else {
			$settings = [];
		}

		$id = pathinfo($file, PATHINFO_FILENAME);

		if (!$_POST && !empty($settings[$id])) {
			$_POST['settings'] = $settings[$id];
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	if (isset($_POST['save'])) {

		try {

			$settings[$id] = $_POST['settings'];

			file_put_contents(FS_DIR_APP . 'vmods/' . '.settings', json_encode($settings, JSON_UNESCAPED_SLASHES), LOCK_EX);

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	breadcrumbs::add(basename($_GET['vmod']));
?>
<style>
pre {
	background: #f9f9f9;
	border-radius: 4px;
	overflow: auto;
	max-width: 100%;
	max-height: 400px;
}

.operation {
	border: 1px solid #f3f3f3;
	border-radius: 4px;
	padding: 1em;
	margin-bottom: 1em;
}
</style>

<div class="panel panel-app">
	<div class="panel-heading">
		<?php echo $app_icon; ?> <?php echo language::translate('title_configure_vmod', 'Configure vMod'); ?>
	</div>

	<div class="panel-body">

		<h1><?php echo $id; ?></h1>

		<?php echo functions::form_draw_form_begin('settings_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

			<table class="table table-striped">
				<tbody>
					<?php foreach ($xml->setting as $setting) { ?>
					<tr>
						<td style="width: 50%">
							<strong><?php echo $setting->title; ?></strong>
							<?php echo !empty($setting->description) ? '<div>'. $setting->description .'</div>' : ''; ?>
						</td>
						<td style="width: 50%">
							<?php if (!empty($setting->multiple)) { ?>
							<?php echo functions::form_draw_function($setting->function, 'settings['.$setting->key.'][]', true); ?>
							<?php } else { ?>
							<?php echo functions::form_draw_function($setting->function, 'settings['.$setting->key.']', true); ?>
							<?php } ?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="panel-action">
				<div class="btn-group">
					<?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
					<?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?>
					<?php echo functions::form_draw_button('uninstall', language::translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?>
				</div>
			</div>

		<?php echo functions::form_draw_form_end(); ?>
	</div>
</div>