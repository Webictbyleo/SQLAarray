<?php
defined('SAFE')or die('Not allowed');
/**
Advanced sql Like array parsing and creation tool created by Leo Anthony
This project is still under review and not an opensource.
However,if you find the need to use this work on your 
project,please contact the author 
ifayce@gmail.com. Proudly Nigerian!
*/
//Load the dependencies
Director::profile('Linq');
Director::profile('timepro');
		class sqlArrayV2{
		
		private $sql,
			$database,
			$is_associative,
			$wagons = array(
			'FROM'=>'',
			'LIMIT'=>'',
			'WHERE'=>'',
			'AND'=>'',
			'OR'=>'',
			'ORDER'=>'',
			'COLS'=>array(),
			),
			$orderOfappearance = array(),
			$order = array(),
			$tablename = 'sequenceV',
			$conditionals,
			$pointer = 0,
			$count,
			$columns,
			$search,
			$view,
			$result;
			
			

			private static $registry = array();
		
		public function __construct(){
		//Mimic the table name
				$tb = preg_match_all("/[a-zA-Z]/",$this->tablename.uniqid(true),$alpha);
				$tb = implode('',$alpha[0]);
				$this->tablename = $tb.time();
				$this->view = clone($this);
		}
				//Query Clauses
			function SELECT($selections){
				
				$this->order[0] = 'SELECT';
				$this->wagons['COLS'] = $selections;
			return $this;
			}
			
			
			function DELETE(){
			$this->order[0] = 'DELETE';
			$this->wagons['COLS'] = '';
			return $this;
			}
			function INSERT(){
			$this->order[0] = 'INSERT';
			$this->wagons['COLS'] = '';
			return $this;
			}
			function UPDATE(){
			$this->order[0] = 'UPDATE';
			$this->wagons['COLS'] = '';
			return $this;
			}
			function SET(array $data){
						if($this->order[0] =='UPDATE'){
				$this->order[1] .= ' SET ';
				$set = array();
				foreach($data as $key=>$val){
				$set[] = $key.'='."'$val'";
				}
				$this->order[1] .= implode(',',$set);
				}elseif($this->order[0] =='INSERT'){
					foreach($data as $key=>$vals){
					$ve[] = "'$vals'";
					}
					$set = '('.implode(',',array_keys($data)).')';
					$values = '('.implode(',',$ve).')';
					$this->order[1] .= ' '.$set.' VALUES '.$values;
				}
				return $this;
			}
			
			
			function LIMIT($limit=false,$offset=0){
				//Build the sql
				$this->sql = $this->buildSql();
				if($this->database ==false)
				return false;
				$search = new Linq;
				$search->database($this->database);
				//Perform the query
				
				
				$search->query($this->sql);
				
					$this->count = $search->num_rows();
					$columns = array();
					if($this->count>0){
						
					$this->result = $search->getResult();
						
					if($limit and is_numeric($limit)){
							$this->result = array_slice($this->result,$offset,$limit);
							}
					$keys = array_keys(current($this->result));
					$cr = count($keys);
					$i = 0;
					
					$columns = array_fill_keys($keys,array());
							
							
							$group = array();
							
						for($ii=0;$cr > $ii;$ii++){
								$key = $keys[$ii];
								$row = $columns[$keys];
						$input = $this->result;
							$result	= array();
		foreach ($input as $i => &$in) {
						
					if((is_null($in[$key]) || strlen($in[$key]) ===0)){
					
					$in[$key] = 0;
					}
				if (is_array($in) && isset($in[$key])) {
					$in	= $in[$key];
				} else {
					unset($input[$i]);
				}
				
			}		
			$columns[$key] = $input;
							
						}
						
						
					}
					
					$this->search = $search;
					$this->columns = $columns;
				return (object)$columns;
			}
			//GETTERS
			function ASSOC(){
				
				return ($res = $this->result) ? $res :array();
			}
			function Column(){
			return ($res = $this->columns) ? $res :array();
			}
			public function injectColumn($key,array $vals){
				$this->columns[$key] = $vals;
				
			}
			function MIN($col){
		//Get the min;
		if(!isset($this->result))return false;
			return min($this->columns[$col]);
		return $this;
		}
		function MAX($col){
		//Get the min;
		if(!isset($this->result))return false;
			return max($this->columns[$col]);
		
		}
		
		function Affected_rows(){
			if(!isset($this->search)) return false;
		return $this->search->affected_rows();
		}
		
		function GROUP($by=NULL,$return =NULL){
		if(!isset($this->result)) return false;
			if(!isset($this->registry['group'])){
				while($data=$this->search->fetch_assoc()){
					$key = $data[$by];
						if($return !==NULL && array_key_exists($return,$data)){
							$data = $data[$return];
						}
					if(isset($group[$key])){
					$group[$key][] = $data;
					}else{
					$group[$key] = array($data);
					}
						
				}
				$this->registry['group'] = $this->group = $group;
			}
		return $this->group;
		}
		function AVG($col){
			if(!isset($this->result))return false;
			$sum = array_sum($this->columns[$col]);
			return $sum / count($this->columns[$col]);
		}
		function SUM($col){
		if(!isset($this->result))return false;
		return array_sum($this->columns[$col]);
		}
		function FIRST(){
		if(!isset($this->result))return false;
		return current($this->result);
		}
		function LAST(){
		if(!isset($this->result))return false;
		return end($this->result);
		}
		
		function getTableName(){
		return $this->tablename;
		}
		
		function getCount(){
		return $this->count();
		}
			
			function Count(){
			if(isset($this->result))return $this->count;
			return false;
		}
			//Aliasis for the selected columns
			/**
			The alias method can be used directly in place of  the select statement
				to apply alias directly
			**/
			function _AS(array $alias){
					//Make sure we're setting alias when in select operation
			if(strpos($this->order[0],'SELECT') !==false){
					if($this->wagons['COLS'] !='*'){
					$columns = array_fill_keys(explode(',',$this->wagons['COLS']),"");
					$aliases=array();
					$alias = $alias[0];
					
				foreach($columns as $realCol => $al){
				
					if(array_key_exists($realCol,$alias)){
					
					$columns[$realCol] = $realCol.' AS '.$alias[$realCol];
					}else{
					$columns[$realCol] = $realCol;
					}
				}
				$this->wagons['COLS'] = implode(',',$columns);
				}
			}else{
			//Implement the direct use
			}
				return $this;
			}
			
			function ORDER_BY($col,$direction='DESC'){
			//Make sure we're ordering only in select operation
			if($this->order[0] =='SELECT'){
			$this->order[100] = "ORDER BY $col $direction";
			}
			return $this;
			}
			function FROM($array){
			$this->order[1] = 'FROM '.$this->tablename;
				$this->is_associative = ArrayLib::is_associative($array);
			$this->getObjectArray($array);
			return $this;
			}
			function INTO($array){
			$this->order[1] = 'INTO '.$this->tablename;
			$this->is_associative = ArrayLib::is_associative($array);
			$this->getObjectArray($array);
			
			return $this;
			}
			function WHERE(){
			if($this->order[0] =='INSERT') return $this;
			if($this->wagons['WHERE'] !==1){
				$this->wagons['WHERE'] = 1;
				$this->order[2] = 'WHERE';
			}
			return $this;
			}
			//Aggregator function
			public function _AND(){
				if(in_array('WHERE',$this->order)){
				$this->conditionals[$this->pointer] .= ' AND';
				//Assign the pointer;
				$this->pointer++;
				}
				return $this;
			}
		function _OR(){
			if(in_array('WHERE',$this->order)){
			$this->conditionals[$this->pointer] .= ' OR';
				$this->pointer++;
				}
				return $this;
			}
			
		//Aggregate modifiers
		
		
		function EXPRESS($xpression){
		
			return $this;
		}
		
		private function parse_Expression(){
			
		}
		function _DISTINCT(){
			$this->order[0] .= ' DISTINCT';
			return $this;
		}
		//Validates using the date parts 
		function DATEPART($col,$part='DAY',$operator='EQUAL',$val){
		if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalDATEPART)){
				
				function SQLARRAYvalDATEPART($col,$part,$operator,$val){
					if(strtotime($col) ===false)return false;
				$time = new timepro;
						$date = $time->date('Y-m-d H:i:s');
						$d2 = date('Y-m-d H:i:s');
						$parts = array('SECOND','MINUTE','HOUR','DAY','WEEK','MONTH','YEAR','YDAY');
						$getParts = $time->calc($col);
						$pp = array_combine($parts,array_splice(array_values($getParts),0,count($parts)));
						
						$part = strtoupper($part);
						switch($operator){
					case 'GREATERTHAN':
					case '>':
					$offset = $pp[$part];
					$compute = (boolean)($val > $offset);
					
					break;
					case 'LESSTHAN':
					case '<':
					$offset = $pp[$part];
					$compute = ($val < $offset);
					
					break;
					case 'BETWEEN':
					case 'IN':
					$offset = round($pp[$part]);
					$val = explode(',',$val);
					
					$compute = (boolean)(in_array($offset,$val));
					
					break;
					case 'NOTIN':
					case '!IN':
					case '!BETWEEN':
					$offset = round($pp[$part]);
					$val = explode(',',$val);
					
					$compute = (boolean)(!in_array($offset,$val));
					
					break;
					case 'EQUAL':
					case '==':
					case '=':
					$offset = $pp[$part];
					$compute = (boolean)($offset == $val);
					
					break;
					case 'NOT':
					case '!':
					$offset = $pp[$part];
					$compute = (boolean)($offset !== $val);
					break;
					}
					
						return $compute;
						}
				
				}
					$this->conditionals[$this->pointer] = "SQLARRAYvalDATEPART('.$col.',$part,'$operator',$val)";
		return $this;
		}
		//Validates using the date parts difference
		function DATEDIFF($col,$part='day',$op="GREATER",$val){
		if($this->wagons['WHERE'] !==1) return $this;
				if(!function_exists(SQLARRAYvalDATEDIFF)){
				
					function SQLARRAYvalDATEDIFF($col,$part,$operation,$val){
				
						$time = new timepro;
						$date = $time->date('Y-m-d H:i:s');
						$d2 = date('Y-m-d H:i:s',strtotime($col));
						$diff = $time->diff($d2,$date);
						$parts = array('YEAR','MONTH','WEEK','DAY','HOUR','MINUTE','SECONDS','INTERVAL','TIMESTAMP');
						$pp = array_combine(array_slice($parts,0,count($diff)),array_values($diff));
							$part = strtoupper($part);
					switch($operation){
					case 'GREATERTHAN':
					case '>':
					$offset = $pp[$part];
					
					$compute = (boolean)($offset > $val);
					
					break;
					case 'LESSTHAN':
					case '<':
					$offset = $pp[$part];
					$compute = (boolean)($offset < $val);
					break;
					case 'BETWEEN':
					case 'IN':
					$offset = round($pp[$part]);
					$val = explode(',',$val);
					
					$compute = (boolean)(in_array($offset,$val));
					
					break;
					case 'EQUAL':
					case '==':
					case '=':
					$offset = $pp[$part];
					$compute = (boolean)($offset == $val);
					break;
					case 'NOT':
					case '!':
					$offset = $pp[$part];
					$compute = (boolean)($offset !== $val);
					break;
					}
					return $compute;
				}
				
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalDATEDIFF('.$col.','$part','$op',$val)";
		return $this;
		}
		//Validates using the date parts when subtracted
		function DATESUB($col,$part='0 minute',$operation='=',$val){
		if($this->wagons['WHERE'] !==1) return $this;
		if(!function_exists(SQLARRAYvalDATESUB)){
				
					function SQLARRAYvalDATESUB($col,$part,$operation,$val){
					
								if(strtotime($col)===false)return false;
						$time = new timepro;
						$date = $time->date('Y-m-d H:i:s');
						
						//Subtract the interval from provided date
						$sub = $time->calc('-'.$part,strtotime($val));
						
						$part = strtoupper(end(explode(' ',$part)));
						$parts = $parts = array('SECOND','MINUTE','HOUR','DAY','WEEK','MONTH','YEAR','YDAY');
						
						$pp = array_combine($parts,array_splice(array_values($sub),0,count($parts)));
						$pp['TIMESTAMP'] = $sub[0];
						//Migrate the current column value as value
						$val = $col;
					switch($operation){
					case 'GREATERTHAN':
					case '>':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($val > $offset);
					
					break;
					case 'LESSTHAN':
					case '<':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($val < $offset);
					
					break;
					case 'BETWEEN':
					case 'IN':
					$offset = $pp['TIMESTAMP'];
					$val = explode(',',$val);
					foreach($val as $k => $xv){
					$val[$k] = strtotime($xv);
					}
					$compute = (boolean)(in_array($offset,$val));
					
					break;
					case 'EQUAL':
					case '==':
					case '=':
					$offset = $offset = $pp['TIMESTAMP'];
					$val = strtotime($val);	
					$compute = (boolean)($offset == $val);
					break;
					case 'NOT':
					case '!':
					$offset = $pp['TIMESTAMP'];
					$val = strtotime($val);	
					$compute = (boolean)($offset !== $val);
					break;
					}
					return $compute;
				}
				
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalDATESUB('.$col.','$part','$operation','$val')";
				return $this;
		}
		//Validates using the date parts when added
		function DATEADD($col,$part='0 minute',$operation='=',$val){
		if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalDATEADD)){
				
					function SQLARRAYvalDATEADD($col,$part,$operation,$val){
				if(strtotime($col)===false)return false;
						$time = new timepro;
						$date = $time->date('Y-m-d H:i:s');
						
						
						$sub = $time->calc($part,strtotime($val));
						$part = strtoupper(end(explode(' ',$part)));
						$parts = $parts = array('SECOND','MINUTE','HOUR','DAY','WEEK','MONTH','YEAR','YDAY');
						$pp = array_combine($parts,array_splice(array_values($sub),0,count($parts)));
						$pp['TIMESTAMP'] = $sub[0];
							$col = $val;
					switch($operation){
					case 'GREATERTHAN':
					case '>':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($val > $offset);
					
					break;
					case 'LESSTHAN':
					case '<':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($val < $offset);
					
					break;
					case 'BETWEEN':
					case 'IN':
					$offset = $pp['TIMESTAMP'];
					$val = explode(',',$val);
					foreach($val as $k => $xv){
					$val[$k] = strtotime($xv);
					}
					$compute = (boolean)(in_array($offset,$val));
					
					break;
					case 'EQUAL':
					case '==':
					case '=':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($offset == $val);
					break;
					case 'NOT':
					case '!':
					$val = strtotime($val);	
					$offset = $pp['TIMESTAMP'];
					$compute = (boolean)($offset !== $val);
					break;
					}
					return $compute;
				}
				
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalDATEADD('.$col.','$part','$operation',$val)";
				return $this;
		}
		function _COUNT(){
		
		return $this;
		}
		
		
		//Where operators
		//Validates if col is a class and class name is val
			function ClassName_is($col,$classname){
				if($this->wagons['WHERE'] !==1) return $this;
						self::$registry['Conditionals_args'] = array($this->pointer=>$classname);
						if(!function_exists(SQLARRAYvalClassName_is)){
				function SQLARRAYvalClassName_is($col,$classname){
				
					return is_object($col) AND strtolower(get_class($col)) == strtolower($classname);
				}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalClassName_is('.$col.','$classname')";
			return $this;
			}
			//Validates if col is a class
			function is_Class($col){
				if($this->wagons['WHERE'] !==1) return $this;
				$this->is_Type($col,'class');
				return $this;
			}
			//Validates if col equals value
			function Equal($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			
			if(!function_exists(SQLARRAYval_isEqual)){
			
				function SQLARRAYval_isEqual($col,$val){
				
					if(is_string($val) || is_numeric($val)){
						
						
					$check = ($col == $val);
						
					return $check;
						}
						//Invalidate if not a supported comparision value
						if(is_array($val) or is_object($val))return false;
						return $col === $val;
					}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYval_isEqual('.$col.','$val')";
			return $this;
			}
			//Validates if col is like value
			function Like($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "$col LIKE '%$val%'";
			
			return $this;
			}
			//Validates if col is not like value
			function Not_Like($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
						if(!function_exists(SQLARRAYvalNOT_Like)){
				function SQLARRAYvalNOT_Like($col,$val){
					if(is_string($col) and is_string($val)){
					$col = strtolower($col);
					$val = strtolower($val);
					
					return strpos($col,$val) ===false;
						}
						//Invalidate if not a supported comparision value
						if(is_array($val) or is_object($val))return false;
						return $col !== $val;
					}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalNOT_Like('.$col.',$val)";
			return $this;
			}
			//Validates if col is in the provided list
			function IN($col,$arr){
				
			if($this->wagons['WHERE'] !==1) return $this;
					if(is_array($arr)){
						$arr = implode(',',$arr);
					}
				if(!function_exists(SQLARRAYvalISIN)){
				
				function SQLARRAYvalISIN($col,$arr){
					$arg = func_get_args();
					
					unset($arg[0]);
					
						$arr = explode(',',strtolower(implode(',',$arg)));
						
						$col = strtolower($col);
						
					return in_array($col,$arr);
					}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalISIN('.$col.','$arr')";
			return $this;
			}
				//Validates if col is not in the provided list
			function Not_IN($col,$arr){
			if($this->wagons['WHERE'] !==1) return $this;
			if(is_array($arr)){
						$arr = implode(',',$arr);
					}
			if(!function_exists(SQLARRAYvalISIN)){
				
				function SQLARRAYvalISIN($col,$arr){
					$arg = func_get_args();
					unset($arg[0]);
					
						$arr = explode(',',strtolower(implode(',',$arg)));
						
						$col = strtolower($col);
						
					return in_array($col,$arr);
					}
				}
				
				$this->conditionals[$this->pointer] = "!SQLARRAYvalISNOTIN('.$col.','$arr')";
			return $this;
			}
				//Validates if col is appears in the range
			function Between($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalISBETWEEN)){
				
				function SQLARRAYvalISBETWEEN($col,$val){
					if(strpos($val,'-') ===false)return false;
					
					$ed = explode('-',$val);
						$arr = @range($ed[0],$ed[1]);
						
						$col = strtolower($col);
							if(!is_array($arr))return false;
					return in_array($col,$arr);
					}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalISBETWEEN('.$col.','$val')";
			return $this;
			}
				//Validates if col is not in the provided list
			function Not_Between($col,$val){
		if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalISNOTBETWEEN)){
				
				function SQLARRAYvalISNOTBETWEEN($col,$val){
					if(strpos($val,'-') ===false)return false;
					
					$ed = explode('-',$val);
						$arr = @range($ed[0],$ed[1]);
						
						$col = strtolower($col);
							if(!is_array($arr))return false;
					return in_array($col,$arr);
					}
				}
				
				$this->conditionals[$this->pointer] = "!SQLARRAYvalISNOTBETWEEN('.$col.','$val')";
			return $this;
			}
				//Validates if col is greater than value
			function GreaterThan($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "$col>$val";
			return $this;
			}
			//Validates if col is not greater than value
			function Not_GreaterThan($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "$col<=$val";
			return $this;
			}
			//Validates if col is less than value
			function LessThan($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "$col<$val";
			return $this;
			}
			//Validates if col is not less than value
			function Not_LessThan($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "$col>=$val";
			return $this;
			}
			//Validates if col is a date
			function IsDate($col,$format='Y-m-d H:i:s'){
		if($this->wagons['WHERE'] !==1) return $this;
				if(!function_exists(SQLARRAYvalISVALIDDATE)){
				
				function SQLARRAYvalISVALIDDATE($col,$format){
				
		if ($col instanceof DateTime) {
            return true;
        } elseif (!is_string($col)) {
            return false;
        } elseif (is_null($format)) {
            return false !== strtotime($col);
        }
					$dateFromFormat = DateTime::createFromFormat($format, $col);

        return $dateFromFormat
               && $col === date($format, $dateFromFormat->getTimestamp());
					}
				}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalISVALIDDATE('.$col.','$format')";
			return $this;
			}
			//Validates with the character length
			function CharLen_is($col,$length=0,$operator='EQUALGREATER'){
			if($this->wagons['WHERE'] !==1) return $this;
					if(!function_exists(SQLARRAYvalCharLen_is)){
				function SQLARRAYvalCharLen_is($col,$length,$operator){
				
						if(!is_numeric($length))return false;
						
					switch($operator){
				case 'EQUALGREATER':
				case '>':
				return strlen($col) >= $length;
				break;
				case 'EQUAL':
				case '=':
				case '==':
				
				return strlen($col) == $length;
				break;
				case 'LESSTHAN':
				case '<':
				return strlen($col) < $length;
				break;
				default:
				return strlen($col) >= $length;
				break;
					}
				}
			}
				$this->conditionals[$this->pointer] = "SQLARRAYvalCharLen_is('.$col.',$length,'$operator')";
			return $this;
			}
			//Validates if col is null
			function is_NULL($col){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalEmptyAttAll)){
			function SQLARRAYvalEmptyAttAll($col){
			
					return is_null($col) OR empty($col);
				}
			}
				
				$this->conditionals[$this->pointer] = "SQLARRAYvalEmptyAttAll('.$col.')";
			return $this;
			}
			//Validates if col is not null
			function is_NOTNULL($col){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalNotEmptyAttAll)){
				function SQLARRAYvalNotEmptyAttAll($col){
				//An integer 0 will not be treated as a null value
					return !is_null($col) AND !empty($col) OR strlen($col)>0;
				}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalNotEmptyAttAll('.$col.')";
			return $this;
			}
			
			
			//Validates if col begins with val
			function BeginsWith($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalBeginsWith)){
				function SQLARRAYvalBeginsWith($col,$val){
				//An integer 0 will not be treated as a null value
				
				$val = (string)$val;
				$split = strrpos($col,$val);
					return ($split ===0);
				}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalBeginsWith('.$col.','$val')";
			return $this;
			}
			//Validates if col ends with value
			function EndsWith($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			
			if(!function_exists(SQLARRAYvalENDSWith)){
				function SQLARRAYvalENDSWith($col,$val){
				//An integer 0 will not be treated as a null value
					
						$val = (string)$val;
						
						$split = strrpos($col,$val);
					return ($split !==false and $split+strlen($val) === strlen($col));
				}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalENDSWith('.$col.','$val')";
			return $this;
			}
			//Validates if col contains val
			function Contains($col,$val){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalContains)){
				function SQLARRAYvalContains($col,$val){
				//An integer 0 will not be treated as a null value
				$val = (string)$val;
					return strrpos($col,$val) !==false;
					}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalContains('.$col.','$val')";
			return $this;
			}
			//Validates if col is a file string
			function is_file($col){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "is_file('.$col.')";
			return $this;
			}
			//Validates if col is file and file exists
			function fileExists($col){
			if($this->wagons['WHERE'] !==1) return $this;
				
			$this->conditionals[$this->pointer] = "file_exists('.$col.')";
			return $this;
			}
			//Validates if col is a valid ip type
			function is_ip($col){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalISIP)){
				function SQLARRAYvalISIP($col){
				if((!$data = explode('.',$col)))return false;
				if (filter_var($col, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
		return true;
					}elseif(filter_var($col, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false){
					return true;
					}
					return false;
				}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalISIP('.$col.')";
			return $this;
			}
			//Validates if col is a data type of val
			function is_Type($col,$type='string'){
			if($this->wagons['WHERE'] !==1) return $this;
				if(!function_exists(SQLARRAYvalISTYPE)){
				function SQLARRAYvalISTYPE($col,$type='string'){
				
						switch(strtolower($type)){
				case 'string':
					return is_string($col) && !empty($col);
				break;
				case 'int':
					$pattern = "\d+";
	return preg_match("/^". $pattern . "$/",$col) !==false || is_numeric($col);
	
	
				break;
				case 'bool':
				return is_bool($col) and $col === true||false;
				break;
				case 'array':
				return is_array($col);
				
				break;
				case 'function':
				return is_function($col);
				
				break;
				
				case 'class':
				
				return is_object($col) and !$col instanceof stdClass;
				break;
			}
				
				}
			}
				$this->conditionals[$this->pointer] = "SQLARRAYvalISTYPE('.$col.','$type')";
			return $this;
			}
			//Validates if col exists in the columns
			function colExists($col){
			if($this->wagons['WHERE'] !==1) return $this;
			if(!function_exists(SQLARRAYvalCOLEXISTS)){
					function SQLARRAYvalCOLEXISTS($col,$cols){
						
						return array_key_exists($col,$cols);
					}
				}
					$this->conditionals[$this->pointer] = "SQLARRAYvalCOLEXISTS('$col','.value.')";
				return $this;
			}
			//Validates if col value is an html
			function is_html($col){
				if($this->wagons['WHERE'] !==1) return $this;
				if(!function_exists(SQLARRAYvalISHTML)){
					function SQLARRAYvalISHTML($col){
						$pattern = "<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>.*?<\/\\1>";
						$match = strip_tags($col);
						
						if($match == $col)return false;
						return true;
					}
				}
				$this->conditionals[$this->pointer] = "SQLARRAYvalISHTML('.$col.')";
			return $this;
			}
			//Validates if col an email
			function is_email($col){
			if($this->wagons['WHERE'] !==1) return $this;
			$this->conditionals[$this->pointer] = "filter_var('.$col.',FILTER_VALIDATE_EMAIL)";
			return $this;
			}
			public function __call($method,$params){
				if(method_exists($this,'_'.$method)){
				return $this->{'_'.$method}($params);
				//Accept just only the column name 
				}elseif(function_exists($method)){
				
					
						$ac = count($params);
							if($ac >0){
								if($ac === 1){
									$params = $params[0];
									$call = "$method('$params')";
								}elseif($ac > 1){
									$arg1 = $params[0];
									$arg2 = $params[1];
									
										
										if(strpos($arg2,'\'') !==0 || strpos($arg2,'"') !==0){
											$arg2 = "'$arg2'";
										}
										
									$call = "$method($arg1,$arg2)";
								}
								
				//Use this to force own computation to the columns
					$this->conditionals[$this->pointer] = $call;
							}
				
				}
				return $this;
			}
			private function getObjectArray($arr){
					if(is_object($arr)){
					$this->database = array($arr);
					}elseif(is_string($arr) || is_numeric($arr)){
					$this->database = false;
					return false;
					}
					$this->database = $arr;
					
			}
			private function buildSql(){
				$sql =array();
				//GEt the operation
				$orders = $this->order;
				
				$operation = current($orders);
				//Get the colums we're selecting
				$columns = $this->wagons['COLS'];
				//Narrow the obkject getter
				$from = $orders[1];
				
				$tb = $this->tablename;
				$this->tablename = $tb;
				$sql[] = $operation."\r";
				$sql[] = $columns."\r";
				
				
				$sql[] = $from."\r";
				
				//Check if where adding conditions
				if(isset($this->conditionals) and !empty($orders[2])){
				//Prepare the conditinals
				$condi = implode(' ',$this->conditionals);
				$sql[] = $orders[2];
				$sql[] = $condi;
				}
				if(isset($orders[100])){
				$sql[] = $orders[100];
				}
				
				return implode(' ',$sql);
			}
			public function __destruct(){
			//$this->close();
				
			}
			
			public function close(){
			$this->wagons = $this->view->wagons;
				$this->result = $this->view->result;
				$this->order = $this->view->result;
				$this->conditionals = $this->view->conditionals;
				$this->pointer = $this->view->pointer;
				$this->columns = $this->view->columns;
				$this->database = $this->view->database;
				unset($GLOBALS[$this->tablename]);
				
				Registry::getInstance()->set('SQLARRAYV2LAST_SQL',$this->sql);
			}
		}
?>