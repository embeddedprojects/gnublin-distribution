/*
http://lists.netisland.net/archives/plug/plug-1998/msg00112.html

From: Ben Dugan <bdugan@netaxs.com>
To: "David L. Martin" <dlmarti@njcc.com>
Subject: Programming RTS on serial ports directly
Date: Tue, 01 Dec 1998 11:17:54 -0500
Cc: plug@lists.nothinbut.net
Dear All,

Several of you gave me the pointer to use ioctl() to do direct control
of the RTS line on serial ports, and I wanted to thank you again
because it solved my problem quickly!

I've pasted in the short, simple C program that I used to demonstrate
it to myself, in case anybody's interested.  As indicated, its almost
a direct cut & paste from the UPS Howto that David recommended I look
at.  

One minor note: even when we set RTS this way, it is returned LOW when
the program finishes-- I suppose at that point the system does some
clean up and takes control of the port again.  This is fine in my
application, (I want the positive edge) but is a side effect worth
noting.

Thanks again, and have a great PLUG meeting tommorrow night!

Ben
*/

#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <termios.h>
#include <sys/types.h>
#include <sys/time.h>
#include <sys/stat.h>
#include <fcntl.h>

#include <sys/ioctl.h>
#include <signal.h>

/*
 * rts          set or clear the RTS line (once per execution)
 *
 * Usage:       rts <device> <1 or 0>
 *              For example, rts /dev/ttyS1 1 to set RTS line on ttyS1
 *
 * Author:      Ben Dugan, but really just a minor modification of:
 *              Harvey J. Stein <hjstein@math.huji.ac.il>
 *              UPS-Howto, which in turn is:
 *              (but really just a minor modification of Miquel van
 *              Smoorenburg's <miquels@drinkel.nl.mugnet.org> powerd.c
 *
 * Version:     1.0 19981201
 *
 */

/* Main program. */
int main(int argc, char **argv)
{
  int fd;

  int rtsEnable;
  int flags;

  if (argc < 3) {
    fprintf(stderr, "Usage: rts <device> <1 or 0 (RTS high or low)>\n");
    exit(1);
  }

  /* Open monitor device. */
  if ((fd = open(argv[1], O_RDWR | O_NDELAY)) < 0) {
    fprintf(stderr, "upscheck: %s: %s\n", argv[1], sys_errlist[errno]);
    exit(1);}

  /* Get the bits to set from the command line. */
  sscanf(argv[2], "%d", &rtsEnable);
  
  /* Get the 'BEFORE' line bits */
  ioctl(fd, TIOCMGET, &flags);
  fprintf(stderr, "Flags are %x.\n", flags);


  /* Set or clear RTS according to the command line request */

  if(rtsEnable!=0) {
    flags |= TIOCM_RTS;
  }
  else flags &= ~TIOCM_RTS;

  ioctl(fd, TIOCMSET, &flags);
  fprintf(stderr, "Setting %x.\n", flags);

  sleep(1);

  /* Get the 'AFTER' line bits */
  ioctl(fd, TIOCMGET, &flags);
  fprintf(stderr, "Flags are %x.\n", flags);

  
  close(fd);
}
