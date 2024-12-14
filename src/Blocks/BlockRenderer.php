<?php
namespace ClientBlocks\Blocks;

class BlockRenderer {
    public static function execute_php($code, $block) {
        // Create a safe scope for PHP execution
        $result = [];
        
        try {
            // Get global context
            $context = BlockContext::get_context();
            
            // Add block-specific context
            $context['block'] = $block;
            $context['fields'] = get_fields();
            
            // Extract variables into the current scope
            extract($context);
            
            // Execute the PHP code and capture the output
            ob_start();
            eval('?>' . $code);
            $output = ob_get_clean();
            
            // If there's output, add it to the result
            if ($output) {
                $result['output'] = $output;
            }
            
            // Add any variables defined in the PHP code to the result
            $defined_vars = get_defined_vars();
            foreach ($defined_vars as $key => $value) {
                if (!array_key_exists($key, $context) && $key !== 'code' && $key !== 'result') {
                    $result[$key] = $value;
                }
            }
            
            // Merge the context into the result
            $result = array_merge($context, $result);
            
        } catch (\Throwable $e) {
            // Log the error and return an error message
            error_log('Block PHP execution error: ' . $e->getMessage());
            $result['error'] = 'Error executing block PHP code';
        }
        
        return $result;
    }
}
