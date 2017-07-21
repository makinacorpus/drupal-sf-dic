<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('processed', null, InputOption::VALUE_NONE, "Set this option if you wish to generate entity with Drupal schema processed (default is to generate it with unprocessed schema)")
            ->addOption('map', null, InputOption::VALUE_OPTIONAL, "Value map, format is: NAME:ALIAS[,NAME:ALIAS]", ' ')
            ->addOption('type-map', null, InputOption::VALUE_OPTIONAL, "Type map, format is: NAME:TYPE[,NAME:TYPE]", ' ')
            ->addOption('static-map', null, InputOption::VALUE_NONE, "This will add the getAliasMap(), getAllFieldMap() and getColumnMap()")
            ->addOption('exclude', null, InputOption::VALUE_OPTIONAL, "Comma separated list of fields to exclude")
            ->addOption('include', null, InputOption::VALUE_OPTIONAL, "Comma separated list of fields to include")
            ->setDescription('Generate an entity using a Drupal table schema')
        ;
    }

    /**
     * Parse a map
     *
     * @param string $string
     *
     * @return array
     *   Keys value pairs
     */
    private function parseMap($string)
    {
        $ret = [];

        $string = preg_replace('/\s+/', '', $string);

        if (!$string) {
            return $ret;
        }


        foreach (explode(',', $string) as $item) {
            // false and 0 are both invalid
            if (!strpos($item, ':')) {
                throw new \InvalidArgumentException("wrong map format");
            }

            $pieces = explode(':', $item);
            if (2 !== count($pieces)) {
                throw new \InvalidArgumentException("wrong map format");
            }

            $ret[$pieces[0]] = $pieces[1];
        }

        return $ret;
    }

    /**
     * Parse a list
     *
     * @param string $string
     *
     * @return array
     *   Values
     */
    private function parseList($string)
    {
        $ret = [];

        $string = preg_replace('/\s+/', '', $string);

        if (!$string) {
            return $ret;
        }

        foreach (explode(',', $string) as $item) {
            if (!empty($item)) {
                $ret[] = $item;
            }
        }

        return $ret;
    }

    /**
     * Make string displayable as a PHP string
     *
     * @param string $string
     *
     * @return string
     */
    private function escapeString($string)
    {
        return sprintf("'%s'", str_replace('\\', '\\\\', (string)$string));
    }

    /**
     * Transform this to a valid PHP key, value pairs definition
     *
     * @param array $values
     *
     * @return string
     */
    private function toPhpKeyValuePairs(array $values)
    {
        $ret = [];

        foreach ($values as $key => $value) {
            $ret[] = sprintf("%s => %s", $this->escapeString($key), $this->escapeString($value));
        }

        return sprintf("[%s]", implode(', ', $ret));
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

        $unchangedMap = [];
        $aliasMap = $this->parseMap($input->getOption('map'));
        $typeMap = $this->parseMap($input->getOption('type-map'));
        $exclude = $this->parseList($input->getOption('exclude'));

        $methods = [];
        $properties = [];
        $namespace = null;

        if (!module_exists($module)) {
            $output->writeln(sprintf("<error>module '%s' does not exist</error>", $module));
            return -1;
        }

        if ($input->getOption('processed')) {
            $tableSchema = drupal_get_schema($module, $table);
            $command = sprintf("console drupal:generate-entity --processed %s %s %s", $module, $table, $escapedClass);
        } else {
            $tableSchema = drupal_get_schema_unprocessed($module, $table);
            $command = sprintf("console drupal:generate-entity %s %s %s", $module, $table, $escapedClass);
        }

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

        // Validate and process exclusion
        if ($exclude) {
            foreach ($exclude as $name) {
                if (!isset($tableSchema['fields'][$name])) {
                    throw new \InvalidArgumentException(sprintf("field '%s' in excluded fields does not exist in table definition", $name));
                }
                unset($tableSchema['fields'][$name]);
            }
        }

        // Validate field maps
        foreach (array_keys($aliasMap) as $name) {
            if (!isset($tableSchema['fields'][$name])) {
                throw new \InvalidArgumentException(sprintf("field '%s' in alias map does not exist in table definition after exclude/include filtering", $name));
            }
        }
        foreach (array_keys($typeMap) as $name) {
            if (!isset($tableSchema['fields'][$name])) {
                throw new \InvalidArgumentException(sprintf("field '%s' in type map does not exist in table definition after exclude/include filtering", $name));
            }
        }

        foreach ($tableSchema['fields'] as $name => $description) {

            // Populate the static map at the same time
            $originalName = $name;
            if (isset($aliasMap[$name])) {
                $name = $aliasMap[$name];
            } else {
                $unchangedMap[$name] = $name;
            }

            $typeIsForced = false;
            $default = '';
            $getterReturnType = 'mixed';
            $getterCast = '';
            $getterBody = '';
            $nullable = !isset($description['not null']) || !$description['not null'];
            $escapedName = preg_replace('/[^\w]/i', '_', $name);
            $camelCasedName = str_replace('_', '', ucwords($escapedName, "_"));

            if (isset($typeMap[$originalName])) {
                $type = $typeMap[$originalName];
                $typeIsForced = true;
            } else {
                $type = $description['type'];
            }

            switch ($type) {

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
                    if (!$typeIsForced && preg_match('/(_at|_on|ts_|_ts|date_|_date|created|updated|changed|modified)/', $name)) {
                        // Ignore default for dates
                        $getterBody = <<<EOT
        if (\$this->{$escapedName}) {
            return new \\DateTimeImmutable('@'.\$this->{$escapedName});
        }
EOT;
                        $getterReturnType = $nullable ? '\\DateTimeInterface' : 'null|\\DateTimeInterface';
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
                        $default = $this->escapeString($description['default']);
                    }
                    $getterCast = '(string)';
                    $getterReturnType = 'string';
                    break;
            }

            if ('null' !== $getterReturnType && $nullable) {
                $getterReturnType = 'null|' . $getterReturnType;
            }
            if (!$getterBody && $nullable) {
                $getterBody = <<<EOT
        if (isset(\$this->{$escapedName})) {
            return {$getterCast}\$this->{$escapedName};
        }
EOT;
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

        $staticMethod = [];
        if ($input->getOption('static-map')) {

            $staticMethod[] = <<<EOT
    /**
     * Get the aliased fields map
     *
     * @return string[]
     *   Keys are database field names, values are this class property name
     */
    static public function getAliasMap()
    {
        return {$this->toPhpKeyValuePairs($aliasMap)};
    }
EOT;

          $staticMethod[] = <<<EOT
    /**
     * Get the unchanged column names
     *
     * @return string[]
     *   Values are property names (so are column names)
     */
    static public function getColumnMap()
    {
        return {$this->toPhpKeyValuePairs($unchangedMap)};
    }
EOT;

          $staticMethod[] = <<<EOT
    /**
     * Get the whole field map, including aliased and unchanged column names
     *
     * @return string[]
     *   Keys are database field names, values are this class property name
     */
    static public function getAllFieldMap()
    {
        return self::getAliasMap() + self::getColumnMap();
    }
EOT;
        }

        $properties = implode("\n", $properties);
        $methods = implode("\n\n", $methods);
        if ($staticMethod) {
            $staticMethod = implode("\n\n", $staticMethod) . "\n\n";
        } else {
            $staticMethod = '';
        }

        $content = <<<EOT
{$header}
/**
 * Generated code, please do not modify.
 *
 * {$command}
 */
class {$class}
{
{$staticMethod}{$properties}

{$methods}
}
EOT;
        $output->writeln($content);
    }
}
