<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */


namespace Ingenerator\KohanaExtras\DebugBar;


use Doctrine\DBAL\Logging\DebugStack;

class DebugDoctrineQueryTrace extends DebugStack
{
    public function startQuery($sql, array $params = NULL, array $types = NULL)
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $index => $dbg) {
            $params['_dbg'.$index] = $this->formatTraceLine($dbg);
        }
        parent::startQuery($sql, $params, $types);
    }

    protected function formatTraceLine(array $trace)
    {
        $template = ['class', 'type', 'function', 'file', 'line'];

        $line = array_merge(array_flip($template), $trace);

        return sprintf(
            '%s%s%s <br><small>in %s:%s</small>',
            $line['class'],
            $line['type'],
            $line['function'],
            $line['file'],
            $line['line']
        );
    }
}
