#!/usr/bin/perl -w -n

chomp;
my @comps = split(/,/);
if (@prevcomps) {
  for (my $i=0;$i<@comps;$i++) {
      $comps[$i] = $prevcomps[$i] if $comps[$i] eq "";
        }
        }
        @prevcomps = @comps;
        print join(",",@comps),"\n";
