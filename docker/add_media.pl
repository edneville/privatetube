#!/usr/bin/perl

use strict;
use warnings;
use DBI; 
use DBD::Pg;
use Data::Dumper;
use File::Slurp;
use IPC::Open3;

require( "/usr/local/data/media_common.inc" );

sub present {
	my $dbh   = shift;
	my $path  = shift;
	my $found = 0;

	my $sql = "select * from " . table() . " where path = ?";
	my $sth = $dbh->prepare_cached( $sql );
	$sth->execute( $path );
	if( $sth->fetch() ) {
		$found = 1;
	}
	$sth->finish;
	return( $found );
}

sub video_thumbnail {
	my $media = shift;

	my $position = 0;
	my $pid;
	my @cmd;

	@cmd = ( avutil(), "-i", $media );
	print Dumper( @cmd ), "\n";

	$pid = open3( my $ih, my $oh, my $eh, @cmd );
	while( my $line = <$oh> ) {
		chomp $line;
		next unless $line =~ /^\s+Duration: ([\d:]+)/;
		print "Line is $line";
		print "Match is $1\n";
		my @p = split( /:/, $1 );
		my $time = ( ($p[0]*60*60) + ( $p[1]*60 ) + ($p[2]));

		$position = int($time/10);
		last;
	}
	waitpid( $pid, 0 );

	my ($base) = $media =~ m(.*/([^/]+)$);

	my $output_file = "/tmp/$base.jpg";

	@cmd = ( avutil(), "-i", $media, "-vframes", "1", "-filter:v", 'scale=280:-1', "-ss", ( $position =~ /^[0-9]+$/ ? $position : 1 ), $output_file );
	print Dumper( @cmd ), "\n";

	$pid = open3( $ih, $oh, $eh, @cmd );
	waitpid( $pid, 0 );

	if( ! -f $output_file ) {
		return undef;
	}

	my $data = read_file( $output_file );
	unlink( $output_file );

	return( $data );
}

sub loop_input {
	my ($dbh, $mh) = (shift, shift);
	my $sql = "insert into " . table() . " (name, path, thumbnail, type) values (?,?,?,?)";
	my $sth = $dbh->prepare( $sql );

	while( my $media = <> ) {
		chomp $media;
		if( !family_friendly( $media ) ) {
			next;
		}
		print $media, "\n";
		if( $media =~ m|/([^/]+)\.(mp4)$| ) {
			my $name = $1;
			if( !present( $mh, $media ) ) {
				my $thumb = video_thumbnail( $media );
				if( !defined( $thumb ) ) {
					next;
				}
				$sth->bind_param(1, $name );
				$sth->bind_param(2, $media );
				$sth->bind_param(3, $thumb, { pg_type => DBD::Pg::PG_BYTEA });
				$sth->bind_param(4, "mp4" );
				$sth->execute;
			}
		}
		if( $media =~ m|/([^/]+)\.(mp3)$| ) { # track naming
			if( !present( $mh, $media ) ) {
				# $sth->execute( $1, $media, $media, "mp3" );
				# find_music_thumbnail();
			}
		}
		if( $media =~ m|/([^/]+)\.(jpg)$|i ) {
			if( !present( $mh, $media ) ) {
				# $sth->execute( $1, $media, $media, "jpg" );
				# add_image_thumbnail();
			}
		}
		if( $media =~ m|/([^/]+)\.(nef)$|i ) {
			if( !present( $mh, $media ) ) {
				# $sth->execute( $1, $media, $media, "nef" );
				# add_image_thumbnail();
			}
		}

		$dbh->commit;
	}
}

my $dbh = dbconn();
my $mh  = dbconn();

loop_input( $dbh, $mh );
$dbh->commit;
$mh->commit;

