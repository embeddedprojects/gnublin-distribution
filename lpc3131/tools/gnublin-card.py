"""
gnublin-card.py

This is a tool to manage the Micro-SD card for the "Gnublin LPC3131" 
microcontroller board running Embedded Linux. The main tasks are

  - Download of bootloader and root filesystem from a remote server (sorry,
    you need a network connection).  The compiled GNU/Linux kernel and 
    modules are contained in the root filesystem. Optionally the kernel
    can be separately downloaded with "fetch-kernel".

    The downloaded files are stored in a temporary directory ("gnublin-tmp/")
    which is created in the current directory.

  - Deletion of existing partitions of a Micro-SD card and creation of
    new partitions for Gnublin.

  - Ext2 filesystem creation for the root filesystem partition.

  - Write Apex bootloader to Bootit partition.

  - Write root filesystem to ext2 partition.

The homepage of the Gnublin Board is 
 
    http://gnublin.org

History
-------

2011-12-22 
   added check_fdisk_version()


To Do
-----

 - Python module python-pexpect must be installed (check?).

 - Auto check for newer versions of gnublin-card.py.

 - Check for newer versions of the downloadable files.

 - Add sha256 check to all downloadable files.

 - Introduce dotfiles for status information.

 - GNU fdisk (--gnu-fdisk) has a different command sequence for creating 
   partitions than ordinary fdisk.

 - Need a working partition size calculation. Example: 

    Device Boot      Start         End      Blocks   Id  System 
    /dev/sdb2               1           2        8032   df  BootIt
    /dev/sdb1               3         487     3911733   83  Linux

   BootIt hat 2 cyl * 8225280 byte (16M).
   Linux has 485 cyl * 8225280 byte.

   See also the sfdisk output.

Hubert Hoegl, 2011 <Hubert.Hoegl@hs-augsburg.de>
"""

VERSION = "6 (2011-12-22T11:12)"

DEV = '/dev/sdb'  # default SD card device
# starting fdisk in GNU mode avoids "Warning:" messages 
FDISK = '/sbin/fdisk'
FDISK_TYPE = None  # 2: GNU Fdisk, 1: util-linux-ng
DEFAULT_APEX = 'apex.bin'
DEFAULT_KERNEL = 'kernel.tar.gz'
DEFAULT_ROOTFS = 'rootfs.tar.gz'
TMPDIR = 'gnublin-tmp'
WEBDIR = "http://www.gnublin.org/downloads/"
RSYNC_ROOTFS_DIR = "elk.informatik.fh-augsburg.de:/srv/rtlabor/eplpc3131/rootfs-git/rootfs"

import getopt
import os
import pexpect
import re
import string
import sys
import time

sw_dev = DEV
sw_tmpdir = TMPDIR
sw_info = None
sw_help = None
sw_delete = None
sw_verbose = None
sw_mkparts = None
sw_apex = None
sw_apex_path = DEFAULT_APEX
sw_mke2 = None
sw_chkext2 = None
sw_root = None
sw_cmdline = None
sw_version = None
sw_umount = None
sw_all = None
sw_fetch_kernel = None
sw_fetch_apex = None
sw_fetch_rootfs = None   
sw_update = None


def check_fdisk_version():
    ''' 
    The version output of GNU fdisk is (Debian):

       hhoegl@aspire1:~$ /sbin/fdisk -v
       fdisk (util-linux-ng 2.13.1.1)

    or, on Ubuntu

       hhoegl@aspire1:~$ /sbin/fdisk -v
       GNU Fdisk 1.2.4
    '''
    global FDISK_TYPE
    cmd = "/sbin/fdisk -v"
    if sw_verbose:
        print "check_fdisk_version:", cmd
    child = pexpect.spawn (cmd)
    child.expect('\r\n')
    #alternative: child.expect(pexpect.EOF, timeout=None)
    version = child.before

    mobj = re.match("fdisk \(util-linux-ng (.*)\)", version)
    if mobj:
        rev = mobj.group(1)
        FDISK_TYPE = 1

    mobj = re.match("GNU Fdisk (.*)", version)
    if mobj: 
        rev = mobj.group(1)
        FDISK_TYPE = 2

    if sw_verbose:
        print "check_fdisk_version: type=%d, version=%s" % (FDISK_TYPE, rev)

    if FDISK_TYPE == None:
        print "Unknown fdisk version: %s" % version
        return 0
    else:
        return 1


def read_until_prompt(F):
    '''Read expect output until the next fdisk 'Command' prompt.
    '''
    while 1:    
       i = F.expect('Command', timeout=None)
       if i == 0:
           break
    if sw_verbose:
        print "read_until_prompt: [[[%s]]]"% F.before
    return F.before


def check_fdisk_ng_partinfo(s):
    '''
       Device Boot      Start         End      Blocks   Id  System
    /dev/sdc1               3         246     1959929+  83  Linux
    /dev/sdc2               1           2       16033+  df  BootIt
    '''
    L = re.split("\r", s, re.MULTILINE)
    R = []
    for line in L:
        line2 = string.split(line, "\n")
        for x in line2:
            mobj = re.match("\s*(/dev/[a-z]+)(\d+)\s*(\d+)\s*(\d+)\s*", x)
            if mobj:
                R.append(mobj.groups())
    return R
 

def check_fdisk_gnu_partinfo(s):
    L = re.split("\r\n", s, re.MULTILINE)
    R = []
    for line in L:
        #                dev        part    start   end     blocks  id     type
        mobj = re.match("\s*(/dev/[a-z]+)(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s+(\w+)\s+(\w+)", line)
        if mobj:
            R.append( mobj.groups() )
    return R
                 

def options():
    global sw_help, sw_dev, sw_delete, sw_verbose, sw_mkparts, sw_apex, \
           sw_mke2, sw_apex_path, sw_info, sw_root, sw_cmdline, sw_version, \
           sw_umount, sw_all, sw_fetch_kernel, sw_fetch_apex, \
           sw_fetch_rootfs, sw_tmpdir, sw_chkext2, sw_update
    try:
        opt = getopt.getopt(sys.argv[1:],
                            "hd:vpaeircut:", 
                            ["help", "device=", "delete", 
                             "verbose", "partitions", "apex", "ext2", 
                             "apex-path=", "info", "root", "cmdline", 
                             "version", "all", "fetch-apex", "fetch-kernel",
                             "fetch-rootfs", "tmpdir=", "chkext2", "update"])
        # opt = (L, L)
    except getopt.error, why:
        print why
        sys.exit(0)            
    if opt[0] == []:
        usage()
    for o in opt[0]:
        if o[0] in ['-h', '--help']:
            sw_help = 1
            usage()
            sys.exit(0)
        if o[0] in ['-i', '--info']:
            sw_info = 1
        if o[0] in ['-v', '--verbose']:
            sw_verbose = 1
        if o[0] in ['-d', '--device']:
            sw_dev = o[1]
            print "device = %s" % sw_dev
        if o[0] in ['-t', '--tmpdir']:
            sw_tmpdir = o[1]
        if o[0] in ['--delete']:
            sw_delete = 1
        if o[0] in ['-p', '--partitions']:
            sw_mkparts = 1
        if o[0] in ['-a', '--apex']:
            sw_apex = 1
        #if o[0] in ['--apex-path']:
        #    sw_apex_path = o[1]
        if o[0] in ['-e', '--ext2']:
            sw_mke2 = 1
        if o[0] in ['-e', '--chkext2']:
            sw_chkext2 = 1
        if o[0] in ['-r', '--root']:
            sw_root = 1
        if o[0] in ['-c', '--cmdline']:
            sw_cmdline = 1
        if o[0] in ['-u']:
            sw_umount = 1
        if o[0] in ['--version']:
            sw_version = 1
        if o[0] in ['--all']:
            sw_all = 1
        if o[0] in ['--fetch-apex']:
            sw_fetch_apex = 1
        if o[0] in ['--fetch-kernel']:
            sw_fetch_kernel = 1
        if o[0] in ['--fetch-rootfs']:
            sw_fetch_rootfs = 1
        if o[0] in ['--update']:
            sw_update = 1
    return opt[1]


def usage():
    print """%s [opts]
!!! Check the device name of your SD card. If it is      
!!! different from the default device %s then set it    
!!! with the -d/--device option.                       
!!!                                                      
!!! It may be neccessary to run this tool as user root:  
!!!       sudo python gnublin-card.py [opts]             

-h, --help             This usage text
--version              Print program version: %s
-c, --cmdline          Run tool interactively (commandline)
-i, --info             Print device info 
-d, --device=<device>  Device, default '%s'
-t, --tmpdir=<dir>     Temporary directory, default '%s'
-v, --verbose          Be verbose
--delete               Delete all partitions
-p, --partitions       Make partitions
-e, --ext2             run mke2fs on sd card root partition
--chkext2              Check sd card root partition 
-a, --apex             Write apex bootloader
-r, --root             Write root fs to ext2 partition
-u                     Unmount ext2 partition
--fetch-apex           Fetch apex bootloader from server (%s)
--fetch-rootfs         Fetch root filesystem from server (%s)
--fetch-kernel         Fetch kernel from server (optional) (%s)
--all                  Run all steps to create a Gnublin Micro SD card
--update               Check for newer versions of this script
""" % (sys.argv[0], sw_dev, VERSION, sw_dev, sw_tmpdir, sw_apex_path,\
       DEFAULT_ROOTFS, DEFAULT_KERNEL)
#--apex-path=...        Pathname of apex bootloader (default '%s')


def verbose_print(s):
    if sw_verbose:
       print s


def part_info():
    F = run_fdisk()
    if sw_verbose:
       print F
    msg = read_until_prompt(F)

    if sw_verbose:
       print 'p'
    F.sendline('p')
    msg = read_until_prompt(F)
    if FDISK_TYPE == 2:
        pi = check_fdisk_gnu_partinfo(msg)
    if FDISK_TYPE == 1:
        pi = check_fdisk_ng_partinfo(msg)
    if sw_verbose:
       print 'q'
    F.sendline('q')
    F.expect(pexpect.EOF)  
    return pi
    

def fdisk_info():
    '''Nice printout of part_info().
    FIXME: number of blocks for BootIt partition seems to be wrong. 
    '''
    L = part_info()
    for x in L:
       if FDISK_TYPE == 2:
           print x[0]+x[1], "-->", x[6], "(%s blocks)" % x[4]
       if FDISK_TYPE == 1:
           print x[0]+x[1], x[2], x[3]


def fdisk_mkparts():
    # make Linux partition no. 1, cyl 3 to end
    # I must start with partition 1 because my fdisk does not allow me
    # to enter a partition number after 'n', 'p'. This is different from
    # the description in http://www.lpclinux.com/LPC313x/LPC313xApexMci#Boot.
    # NOTE: 'w' quits fdisk program

    F = run_fdisk() 

    if FDISK_TYPE == 1:
        print "make partition no. 1 (Linux)"
        F.sendline('n')   # new partition
        F.sendline('p')   # primary
        F.sendline('1')  # default ext2
        F.sendline('3')   # start cylinder
        F.sendline(' ')   # end at default last cylinder

        # make bootit partition no. 2, cyl 1 to cyl 2
        print "make partition no. 2 (Bootit)"
        F.sendline('n')
        F.sendline('p')
        F.sendline('2')  # default ext2 (change later to "BootIt")
        F.sendline(' ')     # default start cylinder 1
        F.sendline(' ')     # default end cylinder 2

        F.sendline('t')
        F.sendline('2')
        F.sendline('df')

        F.sendline('w')   # write quits fdisk

    if FDISK_TYPE == 2:
        print "make partition no. 1 (Linux)"
        F.sendline('n')   # new partition
        F.sendline('p')   # primary
        F.sendline('ext2')  # default ext2
        F.sendline('no')  # create filesystem on partition 
        F.sendline('3')   # start cylinder
        F.sendline(' ')   # end at default last cylinder

        # make bootit partition no. 2, cyl 1 to cyl 2
        print "make partition no. 2 (Bootit)"
        F.sendline('n')
        F.sendline('p')
        F.sendline('ext2')  # default ext2 (change later to "BootIt")
        F.sendline('no')    # filesystem on partition 
        F.sendline(' ')     # default start cylinder 0
        F.sendline(' ')     # default end cylinder 2

        F.sendline('t')
        F.sendline('2')
        F.sendline('df')

        F.sendline('w')   # write quits fdisk

    F.expect(pexpect.EOF)  # wait for child exited


def umount(p):
    r = mountcheck(p)
    if sw_verbose:
        print "mountcheck: %s" % r
    if r:
        umount(r)
    cmd = "umount %s" % p
    print cmd
    os.system(cmd)


def check_ext2_partition():
    umount(EXT2DEV)
    cmd = "e2fsck %s" % EXT2DEV
    os.system(cmd)
    

def mke2():
    umount(EXT2DEV)
    cmd = "mke2fs %s" % EXT2DEV
    print cmd
    os.system(cmd)


def write_apex(path):
    enter_temp_dir()
    cmd = "dd if=%s of=%s2 bs=512" % (path, sw_dev)
    print cmd
    os.system(cmd)
    cmd = "sync"
    print cmd
    os.system(cmd)
    leave_temp_dir()


def untar_file(targz_filename):
    '''Untar a package.tar.gz archive. First check if the archive exists. 
    Then checks for the directory name "package" of the unpacked archive.
    If it does not exist, the archive is unpacked.
    '''
    if not os.path.exists(targz_filename):
       print "please download the archive %s" % targz_filename
    else:
       dirname = string.split(targz_filename, '.')[0]     
       if not os.path.exists(dirname):
           cmd = "tar zxvf %s" % targz_filename           
       else:
           print "Directory '%s' exists. Archive is already unpacked." % dirname

    
def write_rootfs(f):
    '''Write root filesystem to SD card
    
       f - Name of the 'xxx.tar.gz' file.
    '''
    enter_temp_dir()
    untar_file(f)

    # mount SD card
    mountdir = "mnt"
    if not os.path.exists(mountdir):
        os.mkdir(mountdir)
    cmd = "mount %s %s" % (EXT2DEV, mountdir)
    print cmd
    os.system(cmd)

    dirname = string.split(f, '.')[0]     
    if not os.path.exists(dirname):
        cmd = "tar zxvf %s" % f
    os.system(cmd)

    dirname = string.split(f, '.')[0]     
    cmd = "cp -rv %s/* %s" % (dirname, mountdir)
    print cmd
    os.system(cmd)

    # unmount SD card
    cmd = "umount %s" % mountdir
    print cmd
    os.system(cmd)

    leave_temp_dir()



def cmdline():
    print "'help' (short 'h') prints a short usage text."
    while 1:
       s = raw_input("gnublin> ")
       r = cmd_prep(s)
       if r:
           break


def cmd_prep(s):
    L = string.split(s)
    for c in L:
        r = cmd_exec(c)
        if r:
            return 1
    return 0


def cmd_exec(s):
   if s in ['h', 'help']:
       cmdline_help()
   elif s in [ 'q', 'quit']:
       return 1
   elif s in [ '1', 'fetch-apex']:
       fetch_apex()
   elif s in [ '2', 'fetch-rootfs']:
       fetch_rootfs()
   elif s in [ '3', 'fetch-kernel']:
       fetch_kernel()
   elif s in [ '4', 'sd-delete-partitions']:
       fdisk_delete_partitions()
   elif s in [ '5', 'sd-create-partitions']:
       fdisk_mkparts()
   elif s in [ '6', 'sd-mke2fs']:
       mke2()
   elif s in [ '7', 'sd-write-apex']:
       write_apex(sw_apex_path)
   elif s in [ '8', 'sd-write-rootfs']:
       write_rootfs(DEFAULT_ROOTFS)
   elif s in [ '9', 'sd-info']:
       fdisk_info()
   elif s in ['10', 'sd-check']:
       check_ext2_partition() 
   elif s in [ 'u', 'sd-umount']:
       umount(EXT2DEV)
   else:
       print "%s: no such command"% s
   return 0    


def cmdline_help():
   print '''
Use either the long or short names (e.g. 'fetch-apex' or '1').  Multiple
commands can be combined on a line, e.g. "1 2 4 5 6 7 8".

help                  h   this help
quit                  q   quit program
fetch-apex            1   fetch apex bootloader (default '%s')
fetch-rootfs          2   fetch root filesystem (default '%s')
fetch-kernel          3   fetch kernel binary and modules (default '%s')
sd-delete-partitions  4   delete partitions
sd-create-partitions  5   make sd card partitions BootIt and ext2
sd-mke2fs             6   create ext2 filesystem on sd card
sd-write-apex         7   write apex to sd card BootIt partition
sd-write-rootfs       8   write root filesystem to sd card 'ext2' partition
sd-info               9   list sd card partitions
sd-umount             u   unmount ext2 sd card partition
   ''' % (sw_apex_path, DEFAULT_ROOTFS, DEFAULT_KERNEL)


def enter_temp_dir():
    '''First check if we are in a directory named sw_tmpdir. If yes,
       stay there. If not, create this directory and cd to it.
    '''
    curdir = os.getcwd()
    if os.path.basename(curdir) == sw_tmpdir:
       return

    if not os.path.exists(sw_tmpdir):
        print "creating temporary directory %s" % sw_tmpdir
        os.mkdir(sw_tmpdir)
        print "entering %s" % sw_tmpdir
        os.chdir(sw_tmpdir)
    else:
        print "directory %s already exists." % sw_tmpdir
        print "entering %s" % sw_tmpdir
        os.chdir(sw_tmpdir)


def leave_temp_dir():
    curdir = os.getcwd()
    if os.path.basename(curdir) == sw_tmpdir:
        return
    print "leaving %s" % sw_tmpdir
    os.chdir("..") 


def fetch_apex_simple():
    import urllib
    urllib.urlretrieve ("%s/apex/%s"% (WEBDIR,sw_apex_path), "%s"%sw_apex_path)

   
def fetch_apex():
    enter_temp_dir()
    if os.path.exists(sw_apex_path):
        print "%s already exists" % sw_apex_path
    else:
        fetch_link("%s/apex/%s"%(WEBDIR, sw_apex_path))
    leave_temp_dir()


def fetch_rootfs_archive():
    enter_temp_dir()
    if os.path.exists(DEFAULT_ROOTFS):
        print "%s already exists" % DEFAULT_ROOTFS
    else:
        fetch_link("%s/%s" % (WEBDIR, DEFAULT_ROOTFS))
    leave_temp_dir()


def fetch_rootfs_gitrepo():
    enter_temp_dir()
    cmd = "rsync -zav --delete --exclude='.git*' -e \"ssh -p 2222\" hhoegl@%s  ." % RSYNC_ROOTFS_DIR
    if sw_verbose:
        print "fetch_rootfs_gitrepo: %s" % cmd
    os.system(cmd)
    leave_temp_dir()
    

def fetch_rootfs():
    fetch_rootfs_archive()
    # fetch_rootfs_gitrepo()


def fetch_kernel():
    kernel_archive = "%s" % DEFAULT_KERNEL
    enter_temp_dir()
    if os.path.exists(kernel_archive):
        print "%s already exists" % kernel_archive
    else:
        fetch_link("%s/kernel-2.6.33/%s" % (WEBDIR, kernel_archive))
    leave_temp_dir()


def fetch_link(link):
    # http://stackoverflow.com/questions/22676/how-do-i-download-a-file-over-http-using-python
    import urllib2

    url = link

    file_name = url.split('/')[-1]
    u = urllib2.urlopen(url)
    f = open(file_name, 'wb')
    meta = u.info()
    file_size = int(meta.getheaders("Content-Length")[0])
    print "Downloading: %s Bytes: %s" % (file_name, file_size)

    file_size_dl = 0
    block_sz = 8192
    while True:
        buffer = u.read(block_sz)
        if not buffer:
            break

        file_size_dl += len(buffer)
        f.write(buffer)
        status = r"%10d  [%3.2f%%]" % (file_size_dl, file_size_dl * 100. / file_size)
        status = status + chr(8)*(len(status)+1)
        print status,

    print
    f.close()


def fdisk_delete_partitions():
    '''Delete all partitions on SD card '''
    partition_info = part_info()
    F = run_fdisk()
    for x in partition_info:
        print "deleting partition %s" % x[0]+x[1]
        F.sendline('d')
        F.sendline(x[1])
    F.sendline('w')   #  write quits fdisk
    F.expect(pexpect.EOF)   # wait for child exited
   

def run_fdisk():
    if FDISK_TYPE == 2:
        cmd = '%s --gnu-fdisk %s' % (FDISK, sw_dev)
    if FDISK_TYPE == 1:
        cmd = '%s %s' % (FDISK, sw_dev)
    if sw_verbose:
        print "run_fdisk: %s" % cmd
    return pexpect.spawn (cmd)


def mountcheck(dev):
    # http://serverfault.com/questions/143084/how-can-i-check-whether-a-volume-is-mounted-where-it-is-supposed-to-be-using-pyt
    #import pprint
    d = {}
    for l in file('/proc/mounts'):
        if l[0] == '/':
            l = l.split()
            d[l[0]] = l[1]
    if d.has_key(dev):
       return d[dev]
    else:
       return None
    #pprint.pprint(d)


def check_update():
    '''check for newer versions of this script. 

       VERSION = "6 (2011-12-22T10:35)"
    '''
    enter_temp_dir()
    fetch_link("%s/gnublin-card.py" % WEBDIR)
    v = get_version_from_file("gnublin-card.py")
    if v:
        revcount, revdate = v
    v = match_version_line("VERSION = \"%s\"" % VERSION) 
    if v:
        (myrevcount, myrevdate) = v
    if myrevcount < revcount:
        print "Use newer %s/gnublin-card.py (rev %d)." % (sw_tmpdir, revcount)
    else:
        print "no update"
    leave_temp_dir


def get_version_from_file(file):
    '''
    Match the line

         VERSION = "6 (2011-12-22T10:35)"

    Return a tuple (revcount, revdate). Return None if the version string
    could not be found.
    '''
    fobj = open(file, "r")
    for line in fobj:
        t = match_version_line(line)
        if t:
            fobj.close()
            return t 
    return None


def match_version_line(line):
    ''' Return a tuple (revcount, revdate) or None.
    '''
    mobj = re.match('VERSION = \"(\d+)\s+\((.*)\)\"', line)
    if mobj:
        a, b = mobj.groups()
        return int(a), b
    else:
        return None


if __name__ == "__main__":
    options()
    EXT2DEV = sw_dev+'1'

    if sw_version:
        print VERSION
        exit(0)

    r = check_fdisk_version()
    if not r:
        exit(1)

    # FIXME Must have a better way to detect if card is not inserted or 
    # if card has other device.
    if not os.path.exists(sw_dev):
        print "Device %s does not exist. Please insert a card." % sw_dev
        exit(1)

    if sw_info or sw_delete or sw_mkparts or sw_mke2 or sw_apex or \
       sw_root or sw_umount or sw_chkext2 or sw_all:
        print "SD card is device '%s' Please say YES" % sw_dev
        yesno = raw_input('Please type YES: ')
        if yesno <> "YES":
            print "program aborted."
            exit(1)

    if sw_info:
        fdisk_info()

    if sw_delete:
        fdisk_delete_partitions()

    if sw_mkparts:
        fdisk_mkparts()

    if sw_mke2:
        mke2()

    if sw_apex:
        write_apex(sw_apex_path)

    if sw_root:
        write_rootfs(DEFAULT_ROOTFS)

    if sw_umount:
        umount(EXT2DEV)

    if sw_fetch_apex:
        fetch_apex()

    if sw_fetch_rootfs:
        fetch_rootfs()

    if sw_fetch_kernel:
        fetch_kernel()

    if sw_chkext2:
        check_ext2_partition()

    if sw_update:
        check_update()

    if sw_all:
        fetch_apex()
        fetch_rootfs()
        fdisk_delete_partitions()
        fdisk_mkparts()
        mke2()
        write_apex(sw_apex_path)
        write_rootfs(DEFAULT_ROOTFS)

    if sw_cmdline:
        cmdline()
    
# vim: et sw=4 ts=4
