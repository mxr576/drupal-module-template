<?php

namespace Pronovix\DrupalModuleTemplate;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class Composer {

  private const MODULE_TEMPLATE_MACHINE_NAME = 'drupal_module_template';
  private const MODULE_TEMPLATE_DISPLAY_NAME = 'Drupal module template';

  /**
   * Generates and fixes module specific files in the end of project creation.
   *
   * @param \Composer\Script\Event $event
   */
  public static function finalizeModuleCreation(Event $event): void {
    $fs = new Filesystem();

    // Collecting and initializing data.
    $vendor_dir_path = $event->getComposer()->getConfig()->get('vendor-dir');
    $project_root_path = dirname($vendor_dir_path);
    $project_name = basename($project_root_path);
    $template_phpcs_dist_path = "{$project_root_path}/drupal_module_template.phpcs.xml";

    $module_machine_name = preg_replace('/[\W]/', '_', $project_name);
    $module_display_name = ucfirst(preg_replace('/[_-]/', ' ', $project_name));
    $module_info_yml_path = "{$project_root_path}/{$module_machine_name}.info.yml";
    $module_composer_json_path = "{$project_root_path}/composer.json";
    $module_phpcs_path = "{$project_root_path}/phpcs.xml";
    $project_info_yml_path = $project_root_path . '/' . self::MODULE_TEMPLATE_MACHINE_NAME . '.info.yml';

    // SETTING UP THE MODULE TEMPLATE.
    // Fixing the name and the content of the info.yml file.
    $fs->rename($project_info_yml_path, $module_info_yml_path);
    $info_yml_array = Yaml::parse(file_get_contents($module_info_yml_path));
    $info_yml_array['name'] = $module_display_name;
    $info_yml_array['description'] = "{$module_display_name} module.";
    file_put_contents($module_info_yml_path, Yaml::dump($info_yml_array));

    // Replacing project specific composer.json with the module specific one and updating its content.
    self::removeTemplatePrefixFromFileNames($project_root_path, ['composer.json']);
    $composer_json = file_get_contents($module_composer_json_path);
    $composer_json = str_replace(self::MODULE_TEMPLATE_MACHINE_NAME,  $module_machine_name, $composer_json);
    $composer_json_object = json_decode($composer_json, FALSE);
    $composer_json_object->name = 'pronovix/' . str_replace('_', '-',  $module_machine_name);
    $composer_json_object->description = $module_display_name . ' module';
    file_put_contents($module_composer_json_path, json_encode($composer_json_object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Update the PHPCS configuration.
    file_put_contents($module_phpcs_path, str_replace(self::MODULE_TEMPLATE_MACHINE_NAME, "{$module_machine_name}_module", file_get_contents($template_phpcs_dist_path)));

    // Other tweaks.
    self::removeTemplatePrefixFromFileNames($project_root_path, ['.editorconfig', '.gitattributes', '.travis.yml']);

    // CONFIGURING THE DEVELOPMENT ENVIRONMENT.
    // Set the containers' prefix.
    $project_dev_env_composer_path = "{$project_root_path}/.dev/composer.json";
    file_put_contents("{$project_root_path}/.dev/.env", str_replace(self::MODULE_TEMPLATE_MACHINE_NAME, "{$module_machine_name}_module", file_get_contents("{$project_root_path}/.dev/.env")));
    // Change the required composer.json
    $project_dev_composer_json = file_get_contents($project_dev_env_composer_path);
    $project_dev_composer_json = str_replace(self::MODULE_TEMPLATE_MACHINE_NAME . '.',  '', $project_dev_composer_json);
    file_put_contents($project_dev_env_composer_path, $project_dev_composer_json);
    // Update module's machine and display name in runner.yml.dist
    $project_dev_runner_path = "{$project_root_path}/.dev/runner.yml.dist";
    $project_dev_runner_yml = file_get_contents($project_dev_runner_path);
    $project_dev_runner_yml = str_replace([self::MODULE_TEMPLATE_MACHINE_NAME, self::MODULE_TEMPLATE_DISPLAY_NAME], [$module_machine_name, "{$module_display_name} module"], $project_dev_runner_yml);
    file_put_contents($project_dev_runner_path, $project_dev_runner_yml);

    // Cleaning up project related files.
    $fs->remove([
      $vendor_dir_path,
      "{$project_root_path}/lib",
      // TODO Add template README.md.
      "{$project_root_path}/README.md",
      "{$project_root_path}/composer.lock",
      $template_phpcs_dist_path,
    ]);
  }

  /**
   * Removes MODULE_TEMPLATE_MACHINE_NAME from file names.
   *
   * @param string $file_directory
   *   Directory where files with the provided names located.
   * @param string[] $file_names
   *   Array of files names inside $file_directory.
   */
  private static function removeTemplatePrefixFromFileNames(string $file_directory, array $file_names): void {
    $fs = new Filesystem();
    foreach ($file_names as $file_name) {
      $fs->rename($file_directory . '/' . self::MODULE_TEMPLATE_MACHINE_NAME . '.' . $file_name, "{$file_directory}/{$file_name}", TRUE);
    }
  }
}
