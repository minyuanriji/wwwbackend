<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 控制台核心应用程序入口
 * Author: zal
 * Date: 2020-05-05
 * Time: 14:56
 */

namespace app\handlers;

use app\logic\ExceptionLogLogic;
use yii\web\Response;

class ErrorHandler extends \yii\web\ErrorHandler
{
    public function init()
    {
        parent::init();
        if (!YII_DEBUG) {
            $this->exceptionView = '@app/views/error/exception.php';
            $this->errorView = '@app/views/error/error.php';
        }
    }

    /**
     * 异常信息转换
     * @param \Exception $exception
     * @return array
     */
    private function formatException($exception)
    {
        $title = $exception->getMessage();
        $title = str_replace('\\\\', '\\', $title);
        $file = $exception->getFile();
        $file = str_replace('\\', '/', $file);
        $line = $exception->getLine();
        $list = $exception->getTrace();
        $newList = [
            "#{$line}: {$file}",
        ];
        foreach ($list as $i => $item) {
            if ($i === 0) {
                continue;
            }
            if (isset($item['file'])) {
                $file = $item['file'];
                $file = str_replace('\\', '/', $file);
                $newList[] = "#{$item['line']}: {$file}";
            } elseif (isset($item['class'])) {
                $class = $item['class'];
                $class = str_replace('\\', '\\', $class);
                $newList[] = "#0: {$class}->{$item['function']}()";
            }
        }
        return [
            'title' => $title,
            'list' => $newList,
        ];
    }

    /**
     * 异常信息处理
     * @param \Exception $exception
     */
    public function handleException($exception)
    {
        $this->exception = $exception;

        // 记录日志
        $errors = $this->getResult();
        $log = new ExceptionLogLogic();
        $log->error($errors['title'], $errors['list']);

        if (YII_DEBUG) {
            return parent::handleException($exception);
        } else {
            if (\Yii::$app->has('response')) {
                $response = \Yii::$app->getResponse();
            } else {
                $response = new Response();
            }
            if (\Yii::$app->request->isAjax) {
                $result = $this->getResult();
                $response->data = [
                    'code' => 500,
                    'msg' => $result['title'],
                    'data' => null,
                    'error' => $result['list'],
                ];
            } else {
                $response->data = $this->renderFile($this->exceptionView, [
                    'handler' => $this,
                ]);
            }
            $response->send();
        }
    }

    /**
     * 错误处理
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws \yii\base\ErrorException
     */
    public function handleError($code, $message, $file, $line)
    {
        return parent::handleError($code, $message, $file, $line);
    }

    /**
     * 获取异常结果
     * @return array
     */
    public function getResult()
    {
        return $this->formatException($this->exception);
    }

    /**
     * Renders call stack.
     * @param \Exception|\ParseError $exception exception to get call stack from
     * @return string HTML content of the rendered call stack.
     * @since 2.0.12
     */
    public function renderCallStack($exception)
    {
        $out = '<ul>';
        $out .= $this->renderCallStackItem($exception->getFile(), $exception->getLine(), null, null, [], 1);
        for ($i = 0, $trace = $exception->getTrace(), $length = count($trace); $i < $length; ++$i) {
            $file = !empty($trace[$i]['file']) ? $trace[$i]['file'] : null;
            $line = !empty($trace[$i]['line']) ? $trace[$i]['line'] : null;
            $class = !empty($trace[$i]['class']) ? $trace[$i]['class'] : null;
            $function = null;
            if (!empty($trace[$i]['function']) && $trace[$i]['function'] !== 'unknown') {
                $function = $trace[$i]['function'];
            }
            // 不输出函数方法参数内容
            // $args = !empty($trace[$i]['args']) ? $trace[$i]['args'] : [];
            $args = [];
            $out .= $this->renderCallStackItem($file, $line, $class, $function, $args, $i + 2);
        }
        $out .= '</ul>';
        return $out;
    }
}
