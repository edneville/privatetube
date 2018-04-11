# vim:syntax=perl:

my @include;
my @exclude;

push( @include, 'films/universal/' );
push( @include, "tv/youtube/" );

push( @exclude, 'tv/youtube/(hell|documentary/disaster)/' );

sub dbconn {
	my $host     = "tubedb";
	my $dbname   = "tubedb";
	my $username = "tube";
	my $password = "tube";
	my $port     = 5432;
	my $source   = "dbi:Pg:host=$host;port=$port;dbname=$dbname";

	return(
		DBI->connect(
			$source, "$username", "$password", {
					AutoCommit => 0
			}
		)
	);
}

sub table {
	return( 'media' );
}

sub avutil {
	return( "/usr/bin/ffmpeg" );
}

sub family_friendly {
	my $path = shift;

	my $found = 0;
	foreach my $regex (@include) {
		if( $path =~ /$regex/ ) {
			$found = 1;
		}
	}
	return( undef ) if( !$found );
	$found = 0;

	foreach my $regex (@exclude) {
		if( $path =~ m|$regex| ) {
			return( undef );
		}
	}
	return( 1 );
}

sub tidy_name {
	my $str = shift;
		
	$str =~ s/\bs\d\de\d\d\b//g; # episodes sNNeNN
	$str =~ s/--+//g;

	return( $str );
}

1;

