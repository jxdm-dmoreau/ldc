#!/usr/bin/perl -w

use HTTP::Request::Common;
use LWP::UserAgent;
use JSON;


$HTTP_SERVER         = 'http://192.168.1.6/ldc';
$RPC_PATH            = '/rpc/';
$RPC_FILE            = 'ldc.php';



sub send_json
{
    my ($action, $json) = @_;
    print "\n[$action]\n ==> $json\n";
    my $ua = LWP::UserAgent->new;
    $ret = $ua->request(POST "$HTTP_SERVER"."$RPC_PATH"."$RPC_FILE", [action => $action, json   => $json ]);
    if ($ret->is_success) {
	print $ret->content."\n";
	return $ret->content;
    }
    print STDERR $ret->status_line, "\n";
}






###############################################################################
# OPERAIONS
###############################################################################

# add_operation
$hash_json_orig = {
    date        => '2009-03-03',
    account     => 1,
    confirm     => 1,
    recurring   => 0,
    description => "coucou c\'est une description",
    confirm     => 1,
    cats        => [ { 'id' => 1, value => 12 },
		    { 'id' => 2, value => 3 } ],
    labels      => [ "leader-price", "carrefour" ]
    };

$json_to_send  = to_json $hash_json_orig;
$json_rcv      = send_json 'add_operation', $json_to_send;
$json_decoded  = from_json $json_rcv;
if (!$json_decoded->{'id'}) {
    exit(1);
}

# get_operation
$get_json = {
    id => $json_decoded->{'id'}
};
$json_to_send  = to_json $get_json;
$json_rcv      = send_json 'get_operation', $json_to_send;
$json_decoded  = from_json $json_rcv;

if ($hash_json_orig->{'date'} ne $json_decoded->{'date'}) {
    print STDERR "ERROR in date\n";
    exit(1);
}
if ($hash_json_orig->{'description'} ne $json_decoded->{'description'}) {
    print STDERR "ERROR in description\n";
    exit(1);
}
if ($hash_json_orig->{'confirm'} ne $json_decoded->{'confirm'}) {
    print STDERR "ERROR in confirm\n";
    exit(1);
}

if ($hash_json_orig->{'labels'}->[0] ne $json_decoded->{'labels'}->[0] && $hash_json_orig->{'labels'}->[0] ne $json_decoded->{'labels'}->[1]) {
    print STDERR "ERROR in labels \n";
    exit(1);
}
if ($hash_json_orig->{'labels'}->[1] ne $json_decoded->{'labels'}->[0] && $hash_json_orig->{'labels'}->[1] ne $json_decoded->{'labels'}->[1]) {
    print STDERR "ERROR in labels \n";
	exit(1);
}
if ($hash_json_orig->{'cats'}->[0]->{'id'} ne $json_decoded->{'cats'}->[0]->{'id'} && $hash_json_orig->{'cats'}->[0]->{'id'} ne $json_decoded->{'cats'}->[1]->{'id'}) {
    print STDERR "ERROR in cats \n";
    exit(1);
}
if ($hash_json_orig->{'cats'}->[0]->{'value'} ne $json_decoded->{'cats'}->[0]->{'value'} && $hash_json_orig->{'cats'}->[0]->{'value'} ne $json_decoded->{'cats'}->[1]->{'value'}) {
    print STDERR "ERROR in cats \n";
    exit(1);
}
if ($hash_json_orig->{'description'} ne $json_decoded->{'description'}) {
    print STDERR "ERROR in description";
    exit(1);
}

# update_operation
$id = $json_decoded->{'id'};
$hash_json_orig = {
    id          => $id,
    date        => '2009-01-21',
    account     => 2,
    confirm     => 0,
    recurring   => 1,
    description => "Bientôt les vacances",
    confirm     => 0,
    cats        => [ { 'id' => 3, value => 33 } ],
    labels      => [ "jie" ]
    };

$json_to_send  = to_json $hash_json_orig;
$json_rcv      = send_json 'update_operation', $json_to_send;
if ($json_rcv != 1) {
    print STDERR "Erreur del";
    exit(1);
}


# get_operation
$get_json = {
    id => $id
};
$json_to_send  = to_json $get_json;
$json_rcv      = send_json 'get_operation', $json_to_send;
$json_decoded  = from_json $json_rcv;

if ($hash_json_orig->{'date'} ne $json_decoded->{'date'}) {
    print STDERR "ERROR in date\n";
    exit(1);
}
if ($hash_json_orig->{'description'} ne $json_decoded->{'description'}) {
    print STDERR "ERROR in description\n";
    exit(1);
}
if ($hash_json_orig->{'confirm'} ne $json_decoded->{'confirm'}) {
    print STDERR "ERROR in confirm\n";
    exit(1);
}

if ($hash_json_orig->{'labels'}->[0] ne $json_decoded->{'labels'}->[0]) {
    print STDERR "ERROR in labels \n";
    exit(1);
}
if ($hash_json_orig->{'cats'}->[0]->{'id'} ne $json_decoded->{'cats'}->[0]->{'id'}) {
    print STDERR "ERROR in cats \n";
    exit(1);
}
if ($hash_json_orig->{'description'} ne $json_decoded->{'description'}) {
    print STDERR "ERROR in description";
    exit(1);
}

# del_operation
$json_to_send  = to_json $get_json;
$json_rcv      = send_json 'del_operation', $json_to_send;
if ($json_rcv != 1) {
    print STDERR "Erreur del";
    exit(1);
}



#get_operations
undef $hash_json_orig;
$hash_json_orig = {
    date        => '2009-01-21',
    account     => 2,
    confirm     => 0,
    recurring   => 1,
    description => "Bientôt les vacances",
    confirm     => 0,
    cats        => [ { 'id' => 3, value => 33 } ],
    labels      => [ "jie" ]
    };
$json_to_send  = to_json $hash_json_orig;
$json_rcv      = send_json 'add_operation', $json_to_send;
$json_decoded  = from_json $json_rcv;
if (!$json_decoded->{'id'}) {
    exit(1);
}
$id1 = $json_decoded->{'id'};
$json_to_send  = to_json $hash_json_orig;
$json_rcv      = send_json 'add_operation', $json_to_send;
$json_decoded  = from_json $json_rcv;
if (!$json_decoded->{'id'}) {
    exit(1);
}
$id2 = $json_decoded->{'id'};

$json_to_send = to_json {date_begin => '2008-01-01', date_end => '2020-01-01'};
$json_rcv      = send_json 'get_operations', $json_to_send;
$json_decoded  = from_json $json_rcv;

$json_to_send = to_json {id => $id1};
$json_rcv      = send_json 'del_operation', $json_to_send;
if ($json_rcv != 1) {
    print STDERR "Erreur del";
    exit(1);
}


$json_to_send = to_json {id => $id2};
$json_rcv      = send_json 'del_operation', $json_to_send;
if ($json_rcv != 1) {
    print STDERR "Erreur del";
    exit(1);
}







exit 0;
# on récupère l'opération créée
$op_id = $hash_json->{'id'};
$hash_json = { id => $op_id };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_GET_OPERATION;
$hash_json = check_json $json;

print STDERR "ERROR in date\n" if $hash_json_orig->{'date'} ne $hash_json->{'date'};
print STDERR "ERROR in value\n" if $hash_json_orig->{'value'} ne $hash_json->{'value'};
print STDERR "ERROR in description\n" if $hash_json_orig->{'description'} ne $hash_json->{'description'};
print STDERR "ERROR in confirm\n" if $hash_json_orig->{'confirm'} ne $hash_json->{'confirm'};
print STDERR "ERROR in cats 0 id\n" if $hash_json_orig->{'cats'}->[0]->{'id'} ne $hash_json->{'cats'}->[0]->{'id'};
print STDERR "ERROR in cats 0 value\n" if $hash_json_orig->{'cats'}->[0]->{'value'} ne $hash_json->{'cats'}->[0]->{'value'};
print STDERR "ERROR in cats 1 id\n" if $hash_json_orig->{'cats'}->[1]->{'id'} ne $hash_json->{'cats'}->[1]->{'id'};
print STDERR "ERROR incats 1 value\n" if $hash_json_orig->{'cats'}->[1]->{'value'} ne $hash_json->{'cats'}->[1]->{'value'};
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);
print STDERR "ERROR in labels 1\n" if ($hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[1] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);


# modifier une opération
$id = $hash_json->{'id'};
$hash_json_orig = {
    id          => $id,
    date        => '2009-01-21',
    value       => -10,
    description => "no comment",
    confirm     => 0,
    cats        => [ { 'id' => 1, value => -10 } ],
    labels      => [ "fourniture", "carrefour" ]
    };

$json      = to_json $hash_json_orig;
$json      = send_json $json, $RPC_UPDATE_OPERATION;
$hash_json = check_json $json;


# on récupère l'opération modifiée
$hash_json = { id => $id };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_GET_OPERATION;
$hash_json = check_json $json;

print STDERR "ERROR in date\n" if $hash_json_orig->{'date'} ne $hash_json->{'date'};
print STDERR "ERROR in value\n" if $hash_json_orig->{'value'} ne $hash_json->{'value'};
print STDERR "ERROR in description\n" if $hash_json_orig->{'description'} ne $hash_json->{'description'};
print STDERR "ERROR in confirm\n" if $hash_json_orig->{'confirm'} ne $hash_json->{'confirm'};
print STDERR "ERROR in cats 0 id\n" if $hash_json_orig->{'cats'}->[0]->{'id'} ne $hash_json->{'cats'}->[0]->{'id'};
print STDERR "ERROR in cats 0 value\n" if $hash_json_orig->{'cats'}->[0]->{'value'} ne $hash_json->{'cats'}->[0]->{'value'};
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[1]);



# suppresion de l'opération
$hash_json = { id => $op_id };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_DEL_OPERATION;
$hash_json = check_json $json;


################################################################################
# CATEGORIES
################################################################################

$hash_json = { father_id => 1, name=>'pneu', color=>"#1234" };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_ADD_CAT;
$hash_json = check_json $json;

$id = $hash_json->{'id'};

$hash_json = { id => $id, father_id => 2, name=>'pneux', color=>"#12345" };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_UPDATE_CAT;
$hash_json = check_json $json;

$hash_json = { id => $id };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_DEL_CAT;
$hash_json = check_json $json;

exit 0;
