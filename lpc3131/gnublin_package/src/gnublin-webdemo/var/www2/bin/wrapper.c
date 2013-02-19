#include <stdio.h>
#include <string.h>
#include <errno.h>

#define FILENAME "/sys/devices/platform/fsl-usb2-udc.0/gadget/lun0/file"

int main(int argc, char *argv[])
{
  FILE *f;

  if(argc < 2)
  {
    fprintf(stderr, "Parameter missing.\n");
    return 1;
  }

  if((f = fopen(FILENAME, "w")) == NULL)
  {
    fprintf(stderr, "Can't open %s: %s\n", FILENAME, strerror(errno));
    return 2;
  }

  fprintf(f, "%s\n", argv[1]);
  fclose(f);

  return 0;
}
