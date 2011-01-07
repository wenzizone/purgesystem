#! /bin/env perl

use POSIX;
use IO::Socket;
use Net::HTTP;

#################################
#  定义网络socket相关常量
#################################
our $QUEUE_SIZE = 5;
our $LOCAL_PORT = 9999;     #定义监听端口号
our $TIME_OUT = 5;

#################################
#  定义缓存清除全局变量
#################################
our $src_file_dir = "/usr/local/squid/cache";       # 定义缓存存放路径


#################################
#  功能性子程序部分
#################################

# 获得url，并调用清除缓存主程序
sub get_url {
    $grp_file = shift;
    my $cmd = "grep -r -a http $src_file_dir | strings|grep \"$grp_file\" |egrep \"(KEY|^http)\"";          # 定义linux的grep命令串
    my @url = `$cmd`;      # 将grep查找到的url放入数组 
    
    foreach (@url) {
        chomp;
        my ($url) = /(http:\/\/.*)/;
        purge_cache($url);
    }   
}

# 清除缓存
sub purge_cache {
    my $url = shift;
    my $conn = Net::HTTP->new(Host => '127.0.0.1') or die $@;
    $conn->write_request(PURGE => $url);
    my($code,$mess,%h) = $conn->read_response_headers;
    print $url,":",$code,"\n";
}

# 清除缓存子线程，接受网络发送请求，调用get_url方法
sub process_purge_cache {
    my $sock = shift;
    while(<$sock>) {
        if(/quit|exit/i) {
            last;
        } else {
            chomp;
            get_url($_);
        }
    }
}

#################################
#  程序主体部分
#################################

# 是否开启debug模式
my $debug = 0;
foreach (@ARGV) {
    if($_ eq "-d") {
        $debug = 1;
    }
}

# 启动daemon模式
if(!$debug) {
    my $child = fork;
    die "can't fork: $!\n" unless defined($child);
    if($child) {
        print "Purge Cache Server Start, Pid = $child.\n";
        exit(0);
    }
    POSIX::setsid();
    close STDIN;
    close STDOUT;
    close STDERR;
    open(STDIN, "</dev/null");
    open(STDOUT, ">/var/log/pcs_err.log");
    open(STDERR, ">&STDOUT");
    select((select(STDOUT), $|=1)[0]);
    $SIG{CHLD} = 'IGNORE';
    umask(0);
}

# 启动监听端口
my $sock = IO::Socket::INET->new(Listen => $QUEUE_SIZE, LocalPort => $LOCAL_PORT, Timeout => $TIME_OUT, Reuse => 1) or die("can't create listen $LOCAL_PORT");

# 通过循环接受网络请求
while(1) {
    next unless my $session = $sock->accept;
    defined(my $pid = fork) or die "Can't fork";
    if($pid == 0) {
        process_purge_cache($session);
        exit(0);
    }
}

exit(0);



