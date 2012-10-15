#!/bin/bash
# Author: Benedikt Niedermayr (niedermayr@embedded-projects.net)
# Board support package builing script



# Parameters 
  ->minimal(ohne gps,webserver, no examples, no audio, --->nix außer Skriptsprachen(python)),no_web, readonly_fs, bootloader,nameserver(gateway),  
  ---> NUR minimal full und bootloader rest unter variables
# Variables
kernelversion,output_dir,
# Nameserver address



# Deciding Stage : In this stage all required/selected adons 
# will be added to the build process. 





# Building Stages
# Now the complete board support package will be built.


# 1.Stage: Build the rootfs for GNUBLIN
# 1a.Stage: Bootloader anhand von config bauen (8MB oder 32MB version)

# 2.Copy some important support files into the rootfs
#   (e.g. example applications(im home ordner),debian packages---->add to packages list???)

--->pre-compiled (examples??)

# 3.Build support folder
#   (e.g. for how-to files,examles,source_code)


# 4. User rights im Wiki nicht hier!!!


# 5. Alles wenn möglich für den Gnblin installer komform machen!
# 6. Auf die CD einen Ordner mit einem Abbild dieser Struktur + alles fertig ordner(für die Oma)





