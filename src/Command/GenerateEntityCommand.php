<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate an entity class using a table schema
 *
 * @codeCoverageIgnore
 */
class GenerateEntityCommand extends DrupalCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('drupal:generate-entity')
            ->addArgument('module', InputArgument::REQUIRED, "Module name (that provides the table)")
            ->addArgument('table', InputArgument::REQUIRED, "Table name")
            ->addArgument('class', InputArgument::REQUIRED, "Fully qualified class name")
            ->setDescription('Generate an entity using a Drupal table schema')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $table = $input->getArgument('table');
        $class = ltrim($input->getArgument('class'), '\\');
        $escapedClass = str_replace('\\', '\\\\', $class);

        $methods = [];
        $properties = [];
        $namespace = null;

        if (!module_exists($module)) {
            $output->writeln(sprintf("<error>module '%s' does not exist</error>", $module));
            return -1;
        }

        $tableSchema = drupal_get_schema_unprocessed($module, $table);
        if (!$tableSchema || !isset($tableSchema['fields'])) {
            $output->writeln(sprintf("<error>table '%s' does not exists</error>", $table));
            return -1;
        }

        if (!preg_match('/^[a-z]+[\w\d_\\\\]*$/i', $class) || false !== strpos('\\\\', $class) || '\\' === substr($class, -1)) {
            $output->writeln(sprintf("<error>class name '%s' is invalid</error>", $class));
            return -1;
        }

        if (false !== ($index = strrpos($class, '\\'))) {
            $namespace = substr($class, 0, $index);
            $class = substr($class, $index + 1);
        }

        foreach ($tableSchema['fields'] as $name => $description) {

            $default = '';
            $getterReturnType = 'mixed';
            $getterCast = '';
            $getterBody = '';
            $nullable = false;
            $escapedName = preg_replace('/[^\w]/i', '_', $name);
            $camelCasedName = str_replace('_', '', ucwords($escapedName, "_"));

            switch ($description['type']) {

                case 'float':
                    if (isset($description['default'])) {
                        $default = (string)(float)$description['default'];
                    }
                    $getterCast = '(float)';
                    $getterReturnType = 'float';
                    break;

                case 'int':
                case 'numeric':
                case 'serial':
                    if (preg_match('/(_at|_on|ts_|_ts|date_|_date|created|updated|changed|modified)/', $name)) {
                        // Ignore default for dates
                        $getterBody = <<<EOT
        if (\$this->{$escapedName}) {
            return new \\DateTimeImmutable('@'.\$this->{$escapedName});
        }
EOT;
                        $getterReturnType = '\\DateTimeInterface';
                    } else {
                        if (isset($description['default'])) {
                            $default = (int)$description['default'];
                        }
                        $getterCast = '(int)';
                        $getterReturnType = 'int';
                    }
                    break;

                default:
                    // All non handled types are dealed as strings. This is
                    // legit because data will always return strings in the
                    // end and it'll work transparently.
                    if (isset($description['default'])) {
                        $default = sprintf("'%s'", str_replace('\\', '\\\\', (string)$description['default']));
                    }
                    $getterCast = '(string)';
                    $getterReturnType = 'string';
                    break;
            }

            if ('null' !== $getterReturnType && $nullable) {
                $getterReturnType = 'null|' . $getterReturnType;
            }

            if ($getterBody) {
                $methods[] = <<<EOT
    /**
     * Get {$name}
     *
     * @return {$getterReturnType}
     */
    public function get{$camelCasedName}()
    {
{$getterBody}
    }
EOT;
            } else {
                $methods[] = <<<EOT
    /**
     * Get {$name}
     *
     * @return {$getterReturnType}
     */
    public function get{$camelCasedName}()
    {
        return {$getterCast}\$this->{$escapedName};
    }
EOT;
            }

            if ($default) {
                $properties[] = <<<EOT
    private \${$escapedName} = {$default};
EOT;
            } else{
                $properties[] = <<<EOT
    private \${$escapedName};
EOT;
            }
        }

        if ($namespace) {
            $header = <<<EOT
<?php

namespace {$namespace};

EOT;
        } else {
$header = <<<EOT
<?php

EOT;
        }

        $properties = implode("\n", $properties);
        $methods = implode("\n\n", $methods);
        $content = <<<EOT
{$header}
/**
 * Generated code, please do not modify.
 *
 * console drupal:generate-entity {$module} {$table} {$escapedClass}
 *
 * @generated
 */
class {$class}
{
{$properties}

{$methods}
}
EOT;
        $output->writeln($content);
    }
}
