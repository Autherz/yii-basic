<?php
/**
 * WorkmanWebSocket 服务相关
 */

namespace app\commands;

use Workerman\Worker;
use yii\console\Controller;
use yii\helpers\Console;
use PHPSocketIO\SocketIO;
use app\models\Todo;

/**
 *
 * WorkermanWebSocket
 *
 * @author durban.zhang <durban.zhang@gmail.com>
 */

class WorkermanWebSocketController extends Controller
{
    public $send;
    public $daemon;
    public $gracefully;

    // 这里不需要设置，会读取配置文件中的配置
    public $config = [];
    private $ip = '127.0.0.1';
    private $port = '2020';

    public function options($actionID)
    {
        return ['send', 'daemon', 'gracefully'];
    }

    public function optionAliases()
    {
        return [
            's' => 'send',
            'd' => 'daemon',
            'g' => 'gracefully',
        ];
    }

    public function actionIndex()
    {
        if ('start' == $this->send) {
            try {
                $this->start($this->daemon);
            } catch (\Exception $e) {
                $this->stderr($e->getMessage() . "\n", Console::FG_RED);
            }
        } else if ('stop' == $this->send) {
            $this->stop();
        } else if ('restart' == $this->send) {
            $this->restart();
        } else if ('reload' == $this->send) {
            $this->reload();
        } else if ('status' == $this->send) {
            $this->status();
        } else if ('connections' == $this->send) {
            $this->connections();
        }
    }

    public function initWorker()
    {

        $io = new SocketIO(2020);
        $io->on('connection', function($socket) use($io){
            $socket->addedUser = false;
            // when the client emits 'new message', this listens and executes
            $socket->on('new message', function ($data)use($socket){
                // we tell the client to execute 'new message'
                $decode = json_decode($data);
                $message = $decode->{'message'};
                $room = $decode->{'room'};
                $socket->broadcast->to($room)->emit('new message', array(
                    'username'=> $socket->username,
                    'message'=> $message
                ));
            });

            $socket->on('register', function($room) use ($socket) {
                $socket->join($room);
            });
         
            // when the client emits 'add user', this listens and executes
            $socket->on('add user', function ($data) use($socket, $io){
            if ($socket->addedUser)
            return;
                global $usernames, $numUsers;
                $decode = json_decode($data);
                $username  = $decode->{'username'};
                $room = $decode->{'room'};
                
                // we store the username in the socket session for this client
                $socket->username = $username;
                ++$numUsers;
                $socket->addedUser = true;
                $io->sockets->to($room)->emit('login', array( 
                    'numUsers' => $numUsers,
                    'room' => $room
                ));
                // echo globally (all clients) that a person has connected
                $socket->broadcast->to($room)->emit('user joined', array(
                    'username' => $socket->username,
                    'numUsers' => $numUsers,
                    'room' => $room
                ));
            });

            // when the client emits 'typing', we broadcast it to others
            $socket->on('typing', function ($room) use($socket) {
                $socket->broadcast->to($room)->emit('typing', array(
                    'username' => $socket->username
                ));
            });

            // when the client emits 'stop typing', we broadcast it to others
            $socket->on('stop typing', function () use($socket) {
                $socket->broadcast->emit('stop typing', array(
                    'username' => $socket->username
                ));
            });

            // when the user disconnects.. perform this
            $socket->on('disconnect', function () use($socket) {
                global $usernames, $numUsers;
                if($socket->addedUser) {
                    --$numUsers;

                // echo globally that this client has left
                $socket->broadcast->emit('user left', array(
                    'username' => $socket->username,
                    'numUsers' => $numUsers
                    ));
                }
        });
        
        });
        // $ip = isset($this->config['ip']) ? $this->config['ip'] : $this->ip;
        // $port = isset($this->config['port']) ? $this->config['port'] : $this->port;
        // $wsWorker = new Worker("websocket://{$ip}:{$port}");

        // // 4 processes
        // $wsWorker->count = 4;

        // // Emitted when new connection come
        // $wsWorker->onConnect = function ($connection) {
        //     echo "New connection\n";
        // };

        // // Emitted when data received
        // $wsWorker->onMessage = function ($connection, $data) {
        //     // Send hello $data
        //     $connection->send('dddd hello ' . $data);
        // };

        // // Emitted when connection closed
        // $wsWorker->onClose = function ($connection) {
        //     echo "Connection closed\n";
        // };
    }

    /**
     * workman websocket start
     */
    public function start()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'start';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket restart
     */
    public function restart()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'restart';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket stop
     */
    public function stop()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'stop';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket reload
     */
    public function reload()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'reload';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket status
     */
    public function status()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'status';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket connections
     */
    public function connections()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'connections';

        // Run worker
        Worker::runAll();
    }
}