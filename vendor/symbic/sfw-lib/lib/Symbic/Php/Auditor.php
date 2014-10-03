<?php
class Symbic_Php_Auditor
{

	private $stmts = null;

	public function __construct($code = null)
	{
		if (!is_null($code))
		{
			$this->getASTFromString($code);
		}
	}

	public function parseCode($code)
	{
		return $this->getASTFromString($code);
	}

	public function parseFile($file)
	{
		return $this->getASTFromFile($file);
	}

	public function getFunctionAndMethodCalls()
	{
		$stmtsFilteredList = array();
		if (!is_null($this->stmts))
		{
			$this->filterAstRecursive($stmtsFilteredList, $this->stmts);
		}
		return $stmtsFilteredList;
	}

	private function filterAstRecursive(&$stmtsFilteredList, $stmts)
	{
		if (is_array($stmts))
		{
			// this should be a statement list
			foreach ($stmts as $stmt)
			{
				$this->filterAstRecursive($stmtsFilteredList, $stmt);
			}
		}
		elseif(is_object($stmts))
		{
			$element = $stmts;
			// this is a single statement

			switch ($element)
			{
				case $element instanceof PHPParser_Node_Arg:
					$subNodes = $element->getIterator()->getArrayCopy();
					$this->filterAstRecursive($stmtsFilteredList, $subNodes);
					break;

				case $element instanceof PHPParser_Node_Expr_FuncCall:

					$node = array();

					if ($element->name instanceof PHPParser_Node_Expr_Variable)
					{
						$node['name'] = $element->name->name;
						$node['type'] = 'function_call';
					}
					elseif ($element->name instanceof PHPParser_Node_Name)
					{
						$node['name'] = $element->name->parts[0];
						$node['type'] = 'function_call';
					}
					else
					{
						$node['name'] = $element->name;
						$node['type'] = 'function_call';
					}

					//$node['params'] = $this->getParams($element);

					break;

				case $element instanceof PHPParser_Node_Expr_MethodCall :

					$node = array();
					if ($element->name instanceof PHPParser_Node_Expr_Variable)
					{
						$node['name'] = $element->name->name;
						$node['type'] = 'method_call';
					}
					else
					{
						$node['name'] = $element->name;
						$node['type'] = 'method_call';
					}
					//$node['params'] = $this->getParams($element);
					//$node['var'] = $this->getVars($element);

					break;


				case $element instanceof PHPParser_Node_Expr_Eval :

					$node = array();

					$node['type'] = 'function_call';
					$node['name'] = 'eval';
					//$node['params'] = $element->expr->value;

					break;

				case $element instanceof PHPParser_Node_Expr_Exit :

					$node = array();

					$node['type'] = 'function_call';
					$node['name'] = 'exit';
					//$node['params'] = $element->expr->value;


					break;

				case $element instanceof PHPParser_Node_Expr_New :
					$node = array();

					$node['type'] = 'new';
					$node['class'] = $element->class->parts[0];
					//$node['params'] = $element->class->parts[0];

					break;

				case $element instanceof PHPParser_Node_Expr_Include :

					$node = array();

					$node['name'] = 'function_call';
					$node['type'] = 'include';
					//$node['params'] = $element->expr->value;

					break;

				case $element instanceof PHPParser_Node_Stmt_Echo :

					$node = array();

					$node['type'] = 'function_call';
					$node['name'] = 'echo';
					//$node['params'] = $element->exprs[0]->value;

					break;

				case $element instanceof PHPParser_Node_Expr_ShellExec :
					// backtick operator

					$node = array();

					$node['type'] = 'function_call';
					$node['name'] = 'shell_exec';

					break;

				case $element instanceof PHPParser_Node_Stmt_Return :
					$node = array();
					$node['type'] = 'return';
				break;



				case $element instanceof PHPParser_Node_Stmt_Global :
					$node = array();
					$node['type'] = 'global';
				break;

				case $element instanceof PHPParser_Node_Stmt_Class :
					$node = array();
					$node['type'] = 'class';
				break;

				case $element instanceof PHPParser_Node_Expr_Closure :
					$node = array();
					$node['name'] = $element->name;
					$node['type'] = 'closure';
				break;

				case $element instanceof PHPParser_Node_Expr_ClosureUse :
					$node = array();
					$node['type'] = 'closureUse';
				break;


				case $element instanceof PHPParser_Node_Expr_PropertyFetch :
					$node = array();
					$node['type'] = 'propertyFetch';
				break;

				case $element instanceof PHPParser_Node_Expr_StaticPropertyFetch :
					$node = array();
					$node['type'] = 'staticPropertyFetch';
				break;

				// list of known elements which will not be put into node list:
				case $element instanceof PHPParser_Node_Expr_Array :
				case $element instanceof PHPParser_Node_Expr_ArrayDimFetch :
				case $element instanceof PHPParser_Node_Expr_ArrayItem :
				case $element instanceof PHPParser_Node_Expr_Assign :
				case $element instanceof PHPParser_Node_Expr_AssignBitwiseAnd :
				case $element instanceof PHPParser_Node_Expr_AssignBitewiseOr :
				case $element instanceof PHPParser_Node_Expr_AssignBitwiseXor :
				case $element instanceof PHPParser_Node_Expr_AssignConcat :
				case $element instanceof PHPParser_Node_Expr_AssignDiv :
				case $element instanceof PHPParser_Node_Expr_AssignList :
				case $element instanceof PHPParser_Node_Expr_AssignMinus :
				case $element instanceof PHPParser_Node_Expr_AssignMod :
				case $element instanceof PHPParser_Node_Expr_AssignMul :
				case $element instanceof PHPParser_Node_Expr_AssignPlus :
				case $element instanceof PHPParser_Node_Expr_AssignRef :
				case $element instanceof PHPParser_Node_Expr_AssignShiftLeft :
				case $element instanceof PHPParser_Node_Expr_AssignShiftRight :
				case $element instanceof PHPParser_Node_Expr_BitwiseAnd :
				case $element instanceof PHPParser_Node_Expr_BitewiseNot :
				case $element instanceof PHPParser_Node_Expr_BitwiseOr :
				case $element instanceof PHPParser_Node_Expr_BitwiseXor :
				case $element instanceof PHPParser_Node_Expr_BooleanAnd :
				case $element instanceof PHPParser_Node_Expr_BooleanNot :
				case $element instanceof PHPParser_Node_Expr_BooleanOr :
				case $element instanceof PHPParser_Node_Expr_Cast :
				case $element instanceof PHPParser_Node_Expr_ClassConstFetch :
				case $element instanceof PHPParser_Node_Expr_Clone :
				case $element instanceof PHPParser_Node_Expr_Concat :
				case $element instanceof PHPParser_Node_Expr_ConstFetch :
				case $element instanceof PHPParser_Node_Expr_Div :
				case $element instanceof PHPParser_Node_Expr_Empty :
				case $element instanceof PHPParser_Node_Expr_Equal :
				case $element instanceof PHPParser_Node_Expr_ErrorSuppress :
				case $element instanceof PHPParser_Node_Expr_Greater :
				case $element instanceof PHPParser_Node_Expr_GreaterOrEqual :
				case $element instanceof PHPParser_Node_Expr_Identical :
				case $element instanceof PHPParser_Node_Expr_InstanceOf :
				case $element instanceof PHPParser_Node_Expr_Isset :
				case $element instanceof PHPParser_Node_Expr_LogicalAnd :
				case $element instanceof PHPParser_Node_Expr_LogicalOr :
				case $element instanceof PHPParser_Node_Expr_LogicalXor :
				case $element instanceof PHPParser_Node_Expr_Minus :
				case $element instanceof PHPParser_Node_Expr_Mod :
				case $element instanceof PHPParser_Node_Expr_Mul :
				case $element instanceof PHPParser_Node_Expr_NotEqual:
				case $element instanceof PHPParser_Node_Expr_NotIdentical :
				case $element instanceof PHPParser_Node_Expr_Plus :
				case $element instanceof PHPParser_Node_Expr_PostDec :
				case $element instanceof PHPParser_Node_Expr_PreDec :
				case $element instanceof PHPParser_Node_Expr_PreInc :
				case $element instanceof PHPParser_Node_Expr_Print :
				case $element instanceof PHPParser_Node_Expr_ShiftLeft :
				case $element instanceof PHPParser_Node_Expr_ShiftRight :
				case $element instanceof PHPParser_Node_Expr_Smaller :
				case $element instanceof PHPParser_Node_Expr_SmallerOrEqual :
				case $element instanceof PHPParser_Node_Expr_StaticCall :
				case $element instanceof PHPParser_Node_Expr_Ternary :
				case $element instanceof PHPParser_Node_Expr_UnaryMinus :
				case $element instanceof PHPParser_Node_Expr_UnaryPlus :
				case $element instanceof PHPParser_Node_Expr_Variable :

				case $element instanceof PHPParser_Node_Name_FullyQualified :
				case $element instanceof PHPParser_Node_Name_Relative :

				case $element instanceof PHPParser_Node_Scalar_ClassConst :
				case $element instanceof PHPParser_Node_Scalar_DirConst :
				case $element instanceof PHPParser_Node_Scalar_DNumber :
				case $element instanceof PHPParser_Node_Scalar_Encapsed :
				case $element instanceof PHPParser_Node_Scalar_FileConst :
				case $element instanceof PHPParser_Node_Scalar_FuncConst :
				case $element instanceof PHPParser_Node_Scalar_LineConst :
				case $element instanceof PHPParser_Node_Scalar_LNumber :
				case $element instanceof PHPParser_Node_Scalar_MethodConst :
				case $element instanceof PHPParser_Node_Scalar_NSConst :
				case $element instanceof PHPParser_Node_Scalar_String :
				case $element instanceof PHPParser_Node_Scalar_TraitConst :


				case $element instanceof PHPParser_Node_Stmt_TraitUseAdaptation_Alias:
				case $element instanceof PHPParser_Node_Stmt_TraitUseAdaptation_Precedence:

				case $element instanceof PHPParser_Node_Stmt_Break:
				case $element instanceof PHPParser_Node_Stmt_Case :
				case $element instanceof PHPParser_Node_Stmt_Catch :
				case $element instanceof PHPParser_Node_Stmt_ClassConst :
				case $element instanceof PHPParser_Node_Stmt_ClassMethod :
				case $element instanceof PHPParser_Node_Stmt_Const :
				case $element instanceof PHPParser_Node_Stmt_Continue :
				case $element instanceof PHPParser_Node_Stmt_Declare :
				case $element instanceof PHPParser_Node_Stmt_DeclareDeclare :
				case $element instanceof PHPParser_Node_Stmt_Do :
				case $element instanceof PHPParser_Node_Stmt_Else :
				case $element instanceof PHPParser_Node_Stmt_ElseIf :
				case $element instanceof PHPParser_Node_Stmt_For :
				case $element instanceof PHPParser_Node_Stmt_Foreach :
				case $element instanceof PHPParser_Node_Stmt_Function :
				case $element instanceof PHPParser_Node_Stmt_Goto :
				case $element instanceof PHPParser_Node_Stmt_HaltCompiler :
				case $element instanceof PHPParser_Node_Stmt_If :
				case $element instanceof PHPParser_Node_Stmt_InlineHTML :
				case $element instanceof PHPParser_Node_Stmt_Interface :
				case $element instanceof PHPParser_Node_Stmt_Label :
				case $element instanceof PHPParser_Node_Stmt_Namespace :
				case $element instanceof PHPParser_Node_Stmt_Property :
				case $element instanceof PHPParser_Node_Stmt_PropertyProperty :
				case $element instanceof PHPParser_Node_Stmt_Return :
				case $element instanceof PHPParser_Node_Stmt_Static :
				case $element instanceof PHPParser_Node_Stmt_StaticVar :
				case $element instanceof PHPParser_Node_Stmt_Switch :
				case $element instanceof PHPParser_Node_Stmt_Throw :
				case $element instanceof PHPParser_Node_Stmt_Trait :
				case $element instanceof PHPParser_Node_Stmt_TraitUse :
				case $element instanceof PHPParser_Node_Stmt_TraitUseAdaption :
				case $element instanceof PHPParser_Node_Stmt_TryCatch :
				case $element instanceof PHPParser_Node_Stmt_Unset :
				case $element instanceof PHPParser_Node_Stmt_Use :
				case $element instanceof PHPParser_Node_Stmt_UseUse :
				case $element instanceof PHPParser_Node_Stmt_While :

				case $element instanceof PHPParser_Node_Const :
				case $element instanceof PHPParser_Node_Expr :
				case $element instanceof PHPParser_Node_Name :
				case $element instanceof PHPParser_Node_Param :
				case $element instanceof PHPParser_Node_Scalar :
				case $element instanceof PHPParser_Node_Stmt :

				break;

				default:
					$node = array();
					$node['type'] = get_class($element);
					$subNodes = $element->getIterator()->getArrayCopy();
					$this->filterAstRecursive($stmtsFilteredList, $subNodes);
				break;
			}

			if (isset($node))
			{
				$attributes = $element->getAttributes();
				$node['line'] = $attributes['startLine'];
				$stmtsFilteredList[] = $node;
			}

			$subNodes = $element->getIterator()->getArrayCopy();
			$this->filterAstRecursive($stmtsFilteredList, $subNodes);

		}
	}


	protected function getASTFromFile($filename)
	{
		if (!file_exists($filename))
		{
			$this->stmts = null;
			return false;
		}

		$fileContent = file_get_contents($filename);
		return $this->getASTFromString($fileContent);
	}

	protected function getASTFromString($string)
	{
		$parser = new PHPParser_Parser(new PHPParser_Lexer);

		try
		{
			$this->stmts = $parser->parse($string);
		}
		catch (PHPParser_Error $e)
		{
			echo 'Parse Error: ', $e->getMessage();
			return false;
		}
		return true;
	}


	/**
	 * Gets items from arrays
	 */
	public function getItems($element)
	{
		if (!isset ($element->items))
		{
			return;
		}

		$arguments = array ();

		foreach ($element->items as $item)
		{
			;
			$arguments[$item->key->value] = $item->value->value;
		}
		return $arguments;
	}

	/**
	 * Gets all params
	 */
	public function getParams($element)
	{
		if (!isset ($element->args))
		{
			return;
		}
		$arguments = array ();

		foreach ($element->args as $arg)
		{
			if (isset ($arg->value->name->parts[0]))
			{
				$arguments[] = $arg->value->name->parts[0];
			}

			if (isset ($arg->value->value))
			{
				$arguments[] = $arg->value->value;
			}
		}
		return $arguments;
	}

	/**
	 * Gets all calling variables
	 */
	public function getVars($element)
	{
		if (isset ($element-> var))
		{
			$arguments = $this->getVars($element-> var);
		}

		if (isset ($element-> class))

			{
			$arguments[]['calling_object'] = $element-> class->parts[0];
		}

		if (is_object($element->name))

			{
			$arguments[]['calling_object'] = $element->name->parts[0];
		}
		else
		{
			$arguments[]['calling_object'] = $element->name;
		}

		return $arguments;
	}
}