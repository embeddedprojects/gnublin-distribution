#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <unistd.h>
#include <time.h>

unsigned char buffer[256];

void select_gpa1()
{
   /* select ADC 1 */
   fd = open("/dev/lpc313x_adc", O_RDWR);
   write(fd, "1", 2);  /* "1\0" */
   close(fd);
}

int get_adc()
{
   fd = open("/dev/lpc313x_adc", O_RDONLY);
   n = read(fd, buffer, 256);
   close(fd);
   return n;
}
