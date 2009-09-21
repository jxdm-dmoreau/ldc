#!/usr/bin/perl -w

use HTTP::Request::Common;
use LWP::UserAgent;
use JSON;


$HTTP_SERVER         = 'http://192.168.1.6/ldc';
$RPC_PATH            = '/rpc/';
$RPC_ADD_OPERATION    = 'RPC_add_operation.php';
$RPC_UPDATE_OPERATION = 'RPC_update_operation.php';
$RPC_DEL_OPERATION    = 'RPC_del_operation.php';
$RPC_GET_OPERATION    = 'RPC_get_operation.php';

$RPC_ADD_CAT         = 'RPC_add_cat.php';
$RPC_UPDATE_CAT      = 'RPC_update_cat.php';
$RPC_DEL_CAT         = 'RPC_del_cat.php';
$RPC_GET_CAT         = 'RPC_get_cat.php';



sub send_json
{
    my ($json, $url) = @_;
    print "\n[$url]\n ==> $json\n";
    my $ua = LWP::UserAgent->new;
    $ret = $ua->request(POST "$HTTP_SERVER"."$RPC_PATH"."$url", [json   => $json ]);
    if ($ret->is_success) {
	return $ret->content;
    }
    print STDERR $ret->status_line, "\n";
}

sub check_json
{
    my ($json) = @_;
    print "<== $json\n";
    my $hash_json = from_json $json;
    if ($hash_json->{'result'} eq 'true') {
	return $hash_json;
    }
    print STDERR "ERROR - $json\n";
    exit 1;
}

# ajouter une opération
$hash_json_orig = {
    date        => '2009-03-03',
    value       => 15,
    account     => 1,
    confirm     => 1,
    recurring   => 0,
    description => "coucou c\'est une description",
    confirm     => 1,
    cats        => [ { 'id' => 1, value => 12 },
		    { 'id' => 2, value => 3 } ],
    labels      => [ "leader-price", "carrefour" ]
    };

$json      = to_json $hash_json_orig;
$json      = send_json $json, $RPC_ADD_OPERATION;
$hash_json = check_json $json;


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
exit 0;

$hash_json = { id => $id };
$json      = to_json $hash_json;
$json      = send_json $json, $RPC_DEL_CAT;
$hash_json = check_json $json;

exit 0;
