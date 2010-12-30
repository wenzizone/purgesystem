#! /bin/env perl

use POSIX;
use IO::Socket;
use File::Find;
use Net::HTTP;

# 网络socket相关
our $QUEUE_SIZE = 5;
our $LOCAL_PORT = 9999;
our $TIME_OUT = 5;

# 清除缓存相关
our @src_file_dir = ("/usr/local/squid/cache");
our $grp_file ="";

# 查找缓存路径下的所有cache文件
sub wanted {
    !-d && search($File::Find::name);
}

# 打开每一个缓存文件,查找匹配项,进行清除
sub search {
    my $filename = shift;
    open(FH, "strings $filename |");

    while (<FH>) {
        chomp;
        purge_cache($_) if(/$grp_file/i and /^http/);
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

# 清除缓存子线程
sub process_purge_cache {
    my $sock = shift;
    while(<$sock>) {
        if(/quit|exit/i) {
            last;
        } else {
            chomp;
            $grp_file = $_;
            find(\&wanted, @src_file_dir);
        }
    }
}

# Main

my $debug = 0;
foreach (@ARGV) {
    if($_ eq "-d") {
        $debug = 1;
    }
}

# Go to Daemon
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

my $sock = IO::Socket::INET->new(Listen => $QUEUE_SIZE, LocalPort => $LOCAL_PORT, Timeout => $TIME_OUT, Reuse => 1) or die("can't create listen $LOCAL_PORT");

# Accept loop
while(1) {
    next unless my $session = $sock->accept;
    defined(my $pid = fork) or die "Can't fork";
    if($pid == 0) {
        process_purge_cache($session);
        exit(0);
    }
}

exit(0);


