#!/usr/bin/perl

use strict;
use warnings;
use FCGI;
use CGI;
use Image::Size;
use Data::Dumper;

local our $q;
my $request = FCGI::Request();
my $target  = 200;
my $base    = "/pool0";
my $max     = 200;

sub main {
	my $q = shift;

	print $q->header(
		-expires => "0m",
		"-Cache-Control" => "max-age=0,private",
	);

	open( my $fh, "<", "../data/thumbnails" ) || print "<pre>error reading data: $!</pre>";
	my @lines = <$fh>;
	close( $fh );

	my $num = scalar( @lines );

	my @rands;

	my $in_count = $q->param( 'count' );
	my $limit    = $q->param( 'limit' );

	if( defined( $in_count ) && int( $in_count ) >= $max ) {
		return;
	}

	if( defined( $limit ) && $limit ne "" ) {
		my $num = 0;
		foreach( @lines ) {
			my @parts = split( /\0/, $_ );
			$num++;

			if( $parts[0] =~ /$limit/ ) {
				push( @rands, $num );
			}
		}
	}
	else {
		for( my $i = 0; $i<20; ) {
			my $candidate = int( rand( $num ) );
			foreach( @rands ) {
				if( $_ eq $candidate ) {
					next;
				}
			}
			push( @rands, $candidate );
			$i++;
		}
	}

	foreach( @rands ) {
		my @parts = split( /\0/, $lines[$_] );

		my ($x,$y) = ( $parts[3], $parts[4] );
		my $ratio  = $target / $x;
		my $height = int( $y * $ratio );

		print "<img onclick=\"javascript:set_player_data( '${parts[2]}' )\" src=\"/${parts[0]}\" width=\"${target}\" height=\"${height}\"><br />\n";
	
	}
}

while( $request->Accept() >= 0 ) {
        my $e = $request->GetEnvironment();
        undef @CGI::QUERY_PARAM;
        $q = CGI->new();

        main( $q );
}

