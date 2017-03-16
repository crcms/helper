<?php

/**
 * IP转为数值型
 * @param string $ip
 * @return string
 */
function ip_long($ip)
{
    return sprintf("%u",ip2long($ip));
}

/**
 * 数值转为IP
 * @param numeric $proper_address
 * @return string
 */
function long_ip($proper_address)
{
    return long2ip($proper_address);
}



/**
 * 静态资源
 * @param string $file
 * @return string
 */
function static_asset($file = null)
{

    static $staticAssetUrl;

    if (empty($staticAssetUrl))
    {
        $staticAssetUrl = rtrim(env('CDN_URL',str_replace('\\','/',dirname(env('SCRIPT_NAME')))),'/');
    }

    return "{$staticAssetUrl}/{$file}";
}


/**
 * @param string $ip
 * @return mixed
 */
function is_ipv4(string $ip)
{
    return filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4);
}


/**
 * @param string $mac
 * @return mixed
 */
function is_mac(string $mac)
{
    return filter_var($mac,FILTER_VALIDATE_MAC);
}


/**
 * @param string $ip
 * @return mixed
 */
function is_ipv6(string $ip)
{
    return filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);
}

/**
 * 废弃，不再使用
 * @param string $ip
 * @return int|string
 */
function ip_format(string $ip)
{
    if (is_ipv4($ip))
    {
        return ip_long($ip);
    }

    if (is_ipv6($ip))
    {
        return $ip;
    }

    return 0;
}


/**
 * ip转化数值数字
 * @param string $ip
 * @return string
 */
function ip_format_number_string($ip) : string
{
    if (is_ipv4($ip)) {
        return (string)ip_long($ip);
    }

    return (string)$ip;
}



function carbon_parse($time = null) : \Carbon\Carbon
{
    return \Carbon\Carbon::parse($time,config('app.timezone'));
}


function carbon_create_from_timestamp(int $timestamp) : \Carbon\Carbon
{
    return \Carbon\Carbon::createFromTimestamp($timestamp,config('app.timezone'));
}


function carbon_now() : \Carbon\Carbon
{
    return \Carbon\Carbon::now(config('app.timezone'));
}


function carbon_create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null) : \Carbon\Carbon
{
    return \Carbon\Carbon::create($year, $month, $day, $hour, $minute , $second ,config('app.timezone'));
}

/**
 * 过滤数据组的空值
 * @param array $array
 * @return array
 */
function array_empty_filter(array $array) : array
{
    return array_filter($array,function($item){
        if (is_array($item)) {
            return array_empty_filter($item);
        }
        return !empty(trim($item));
    });
}



/**
 * 日期格式
 * @param int $timestamp
 * @param number $type
 * @return string
 */
function format_date($timestamp,$type = 3)
{
    switch($type)
    {
        case 1:
            $format = 'Y-m-d';
            break;
        case 2:
            $format = 'Y-m-d H:i';
            break;
        case 3:
            $format = 'Y-m-d H:i:s';
            break;
        case 4:
            $format = 'H:i:s';
            break;
    }
    return date($format,$timestamp);
}



/**
 * 无限级树
 * @param array $data
 * @param number $pid
 * @param number $count
 * @return Ambigous <multitype:, multitype:multitype: , multitype:>
 */
function array_tree(array &$data,$pid = 0,$count = 0,$pidKey = 'parent_id')
{
    if(!isset($data['old']))
    {
        $data = array('new'=>array(),'old'=>$data);
    }
    foreach ($data['old'] as $k => $v)
    {
        if($v[$pidKey]==$pid)
        {
            $v['count'] = $count;
            $data['new'][]=$v;
            unset($data['old'][$k]);
            array_tree($data,$v['id'],$count+1,$pidKey);
        }
    }
    return $data['new'];
}

/**
 *
 * @param array $array
 * @param number $pid
 * @param string $pidKey
 * @author simon
 */
function array_tree_child(array $array, $pid = 0, $pidKey = 'parent_id')
{
    $arr = $tem = array();
    foreach ($array as $v) {
        if ($v[$pidKey] == $pid) {
            $tem = array_tree_child($array, $v['id'], $pidKey);
            //判断是否存在子数组
            $tem && $v['children'] = $tem;
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 *
 * @param array $data
 * @return unknown
 */
function show_tree(array $data,string $pidKey = 'parent_id')
{
    foreach ($data as $key=>&$values)
    {
        $values['delimiter'] = str_repeat('　',$values['count']);
        if($values['count']==0)
        {}
        else
        {
            $next_pid = isset($data[$key+1][$pidKey]) ? $data[$key+1][$pidKey] : 0;
            $next_count = isset($data[$key+1]['count']) ? $data[$key+1]['count'] : 0;
            if ($next_pid != $values[$pidKey] && $next_count != $values['count'])
            {
                $values['delimiter'] .= '└─';
            }
            else
            {
                $values['delimiter'] .= '├─';
            }
        }
    }
    return $data;
}


/**
 *
 * 单位转换，字节转换为常用单位量
 * @param numeric $size => Beat
 * @return string
 */
function unit_conversion($size,$delimiter = '')
{
    return byte_size($size,$delimiter);
}
/**
 * 字节转化为大小
 * @param numeric $byte
 * @param string $delimiter
 * @return string
 */
function byte_size($byte,$delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $byte >= 1024 && $i < 6; $i++) $byte /= 1024;
    return round($byte, 2) . $delimiter . $units[$i];
}
/**
 *
 * 文件大小转换为字节
 * @param numeric $size => Beat
 * @return numeric
 */
function size_byte($size)
{
    if(is_numeric($size)) return $size;
    //获取单位
    $unit = strtoupper(substr($size,-2,2));
    //获取数值
    $size = rtrim($size,$unit);
    switch($unit)
    {
        case 'KB' : $realSize = $size * pow(2,10); break;
        case 'MB' : $realSize = $size * pow(2,20); break;
        case 'GB' : $realSize = $size * pow(2,30); break;
        case 'TB' : $realSize = $size * pow(2,40); break;
        case 'PB' : $realSize = $size * pow(2,50); break;
        default	  : $realSize = 0;
    }
    return $realSize;
}
