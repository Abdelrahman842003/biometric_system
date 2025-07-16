<?php
class ZKTeco {
    private $ip;
    private $port;
    private $socket;
    private $sessionId;
    private $connected = false;
    
    // Command constants
    const CMD_CONNECT = 1000;
    const CMD_EXIT = 1001;
    const CMD_ENABLEDEVICE = 1002;
    const CMD_DISABLEDEVICE = 1003;
    const CMD_RESTART = 1004;
    const CMD_POWEROFF = 1005;
    const CMD_SLEEP = 1006;
    const CMD_RESUME = 1007;
    const CMD_TEST_TEMP = 1011;
    const CMD_TESTVOICE = 1017;
    const CMD_VERSION = 1100;
    const CMD_CHANGE_SPEED = 1101;
    const CMD_AUTH = 1102;
    const CMD_PREPARE_DATA = 1500;
    const CMD_DATA = 1501;
    const CMD_FREE_DATA = 1502;
    const CMD_PREPARE_BUFFER = 1503;
    const CMD_READ_BUFFER = 1504;
    const CMD_USER_WRQ = 8;
    const CMD_USERTEMP_RRQ = 9;
    const CMD_USERTEMP_WRQ = 10;
    const CMD_OPTIONS_RRQ = 11;
    const CMD_OPTIONS_WRQ = 12;
    const CMD_ATTLOG_RRQ = 13;
    const CMD_CLEAR_DATA = 14;
    const CMD_CLEAR_ATTLOG = 15;
    const CMD_DELETE_USER = 18;
    const CMD_DELETE_USERTEMP = 19;
    const CMD_CLEAR_ADMIN = 20;
    const CMD_USERGRP_RRQ = 21;
    const CMD_USERGRP_WRQ = 22;
    const CMD_USERTZ_RRQ = 23;
    const CMD_USERTZ_WRQ = 24;
    const CMD_GRPTZ_RRQ = 25;
    const CMD_GRPTZ_WRQ = 26;
    const CMD_TZ_RRQ = 27;
    const CMD_TZ_WRQ = 28;
    const CMD_ULG_RRQ = 29;
    const CMD_ULG_WRQ = 30;
    const CMD_UNLOCK = 31;
    const CMD_CLEAR_ACC = 32;
    const CMD_CLEAR_OPLOG = 33;
    const CMD_OPLOG_RRQ = 34;
    const CMD_GET_FREE_SIZES = 50;
    const CMD_ENABLE_CLOCK = 57;
    const CMD_STARTVERIFY = 60;
    const CMD_STARTENROLL = 61;
    const CMD_CANCELCAPTURE = 62;
    const CMD_STATE_RRQ = 64;
    const CMD_WRITE_LCD = 66;
    const CMD_CLEAR_LCD = 67;
    const CMD_GET_PINWIDTH = 69;
    const CMD_SMS_WRQ = 70;
    const CMD_SMS_RRQ = 71;
    const CMD_DELETE_SMS = 72;
    const CMD_UDATA_WRQ = 73;
    const CMD_DELETE_UDATA = 74;
    const CMD_DOORSTATE_RRQ = 75;
    const CMD_WRITE_MIFARE = 76;
    const CMD_EMPTY_MIFARE = 78;
    const CMD_GET_TIME = 201;
    const CMD_SET_TIME = 202;
    
    public function __construct($ip, $port = 4370) {
        $this->ip = $ip;
        $this->port = $port;
    }
    
    public function connect() {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->socket) {
            return false;
        }
        
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));
        
        $command = $this->createCommand(self::CMD_CONNECT);
        if ($this->sendCommand($command)) {
            $this->connected = true;
            return true;
        }
        
        return false;
    }
    
    public function disconnect() {
        if ($this->connected && $this->socket) {
            $command = $this->createCommand(self::CMD_EXIT);
            $this->sendCommand($command);
            socket_close($this->socket);
            $this->connected = false;
        }
    }
    
    public function isConnected() {
        return $this->connected;
    }
    
    public function getDeviceInfo() {
        if (!$this->connected) return false;
        
        $info = [];
        
        // Get firmware version
        $command = $this->createCommand(self::CMD_VERSION);
        $response = $this->sendCommand($command);
        if ($response) {
            $info['firmware'] = $this->parseVersion($response);
        }
        
        // Get device status
        $command = $this->createCommand(self::CMD_STATE_RRQ);
        $response = $this->sendCommand($command);
        if ($response) {
            $info['status'] = $this->parseStatus($response);
        }
        
        return $info;
    }
    
    public function reboot() {
        if (!$this->connected) return false;
        
        $command = $this->createCommand(self::CMD_RESTART);
        return $this->sendCommand($command) !== false;
    }
    
    public function setTime($datetime = null) {
        if (!$this->connected) return false;
        if (!$datetime) $datetime = time();
        
        $timeData = pack('V', $datetime);
        $command = $this->createCommand(self::CMD_SET_TIME, $timeData);
        return $this->sendCommand($command) !== false;
    }
    
    public function getTime() {
        if (!$this->connected) return false;
        
        $command = $this->createCommand(self::CMD_GET_TIME);
        $response = $this->sendCommand($command);
        if ($response && strlen($response) >= 4) {
            $timestamp = unpack('V', substr($response, 0, 4))[1];
            return date('Y-m-d H:i:s', $timestamp);
        }
        return false;
    }
    
    public function addUser($userId, $name, $fingerprintTemplate = '', $faceTemplate = '') {
        if (!$this->connected) return false;
        
        // Prepare user data
        $userData = pack('v', $userId) . // User ID (2 bytes)
                   pack('C', 0) . // Role (1 byte)
                   pack('a8', '') . // Password (8 bytes)
                   pack('a24', substr($name, 0, 24)) . // Name (24 bytes)
                   pack('a5', '') . // Card (5 bytes)
                   pack('C', 1); // Group (1 byte)
        
        $command = $this->createCommand(self::CMD_USER_WRQ, $userData);
        $result = $this->sendCommand($command);
        
        if ($result !== false && $fingerprintTemplate) {
            $this->addFingerprint($userId, $fingerprintTemplate);
        }
        
        return $result !== false;
    }
    
    public function deleteUser($userId) {
        if (!$this->connected) return false;
        
        $userData = pack('v', $userId);
        $command = $this->createCommand(self::CMD_DELETE_USER, $userData);
        return $this->sendCommand($command) !== false;
    }
    
    public function addFingerprint($userId, $template) {
        if (!$this->connected) return false;
        
        // This would need proper fingerprint template encoding
        // For now, return true as placeholder
        return true;
    }
    
    public function getAttendanceLogs() {
        if (!$this->connected) return false;
        
        $command = $this->createCommand(self::CMD_ATTLOG_RRQ);
        $response = $this->sendCommand($command);
        
        if (!$response) return false;
        
        $logs = [];
        $offset = 0;
        $recordSize = 16; // Each attendance record is typically 16 bytes
        
        while ($offset + $recordSize <= strlen($response)) {
            $record = substr($response, $offset, $recordSize);
            $log = $this->parseAttendanceRecord($record);
            if ($log) {
                $logs[] = $log;
            }
            $offset += $recordSize;
        }
        
        return $logs;
    }
    
    public function clearAttendanceLogs() {
        if (!$this->connected) return false;
        
        $command = $this->createCommand(self::CMD_CLEAR_ATTLOG);
        return $this->sendCommand($command) !== false;
    }
    
    private function createCommand($commandId, $data = '') {
        $sessionId = $this->sessionId ?? 0;
        $replyId = 0;
        
        $header = pack('vvvv', $commandId, 0, $sessionId, $replyId);
        $packet = $header . $data;
        
        // Calculate checksum
        $checksum = 0;
        for ($i = 0; $i < strlen($packet); $i++) {
            $checksum += ord($packet[$i]);
        }
        $checksum = $checksum % 65536;
        
        return pack('v', $checksum) . $packet;
    }
    
    private function sendCommand($command) {
        if (!$this->socket) return false;
        
        $sent = socket_sendto($this->socket, $command, strlen($command), 0, $this->ip, $this->port);
        if (!$sent) return false;
        
        $response = '';
        $from = '';
        $port = 0;
        $received = socket_recvfrom($this->socket, $response, 1024, 0, $from, $port);
        
        if ($received === false) return false;
        
        // Parse response header
        if (strlen($response) < 8) return false;
        
        $checksum = unpack('v', substr($response, 0, 2))[1];
        $header = unpack('vcommand/vchecksum/vsessionId/vreplyId', substr($response, 2, 8));
        
        $this->sessionId = $header['sessionId'];
        
        return substr($response, 10); // Return data part only
    }
    
    private function parseVersion($data) {
        if (strlen($data) < 4) return 'Unknown';
        return trim($data);
    }
    
    private function parseStatus($data) {
        if (strlen($data) < 20) return [];
        
        $status = unpack('V5', $data);
        return [
            'users_count' => $status[1],
            'records_count' => $status[2],
            'free_users' => $status[3],
            'free_records' => $status[4],
            'free_space' => $status[5]
        ];
    }
    
    private function parseAttendanceRecord($record) {
        if (strlen($record) < 16) return false;
        
        $data = unpack('vuserId/Vdatetime/Ctype/x3', $record);
        
        return [
            'user_id' => $data['userId'],
            'datetime' => date('Y-m-d H:i:s', $data['datetime']),
            'type' => $this->getLogType($data['type'])
        ];
    }
    
    private function getLogType($type) {
        switch ($type) {
            case 0: return 'check_in';
            case 1: return 'check_out';
            case 2: return 'break_out';
            case 3: return 'break_in';
            case 4: return 'overtime_in';
            case 5: return 'overtime_out';
            default: return 'unknown';
        }
    }
    
    public function __destruct() {
        $this->disconnect();
    }
}
?>
