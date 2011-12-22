
#include <stdio.h> 
#include <fcntl.h> 
#include <linux/i2c.h>
#include <linux/i2c-dev.h>

#define ADDR 0x20

int main (int argc, char **argv) { 

    int fd; 
    char filename[32]; 
    char buffer[128]; 
    int n, err; 

    if (argc == 0) {
        printf("usage: %s <device>\n", argv[0]);
		    exit(1);
		}

    sprintf(filename, argv[1]); 
    printf("device = %s\n", filename);

    int slave_address = ADDR; 

    if ((fd = open(filename, O_RDWR)) < 0) { 
        printf("i2c open error"); 
        return -1; 
    } 
    printf("i2c device = %d\n", fd);

    if (ioctl(fd, I2C_SLAVE, slave_address) < 0) { 
        printf("ioctl I2C_SLAVE error"); 
        return -1; 
    } 

    /* slave address is not in buffer */
    buffer[0] = 0x06;  /* command byte: write config regs */ 
    buffer[1] = 0x00;  /* port0 all outputs */
    err = write(fd, buffer, 2);
    if (err != 2) { 
        printf("write: error %d\n", err); 
        return -1; 
    } 
    /* slave address is not in buffer */
    buffer[0] = 0x07;  /* command byte: write config regs */ 
    buffer[1] = 0x00;  /* port0 all outputs */
    err = write(fd, buffer, 2);
    if (err != 2) { 
        printf("write: error %d\n", err); 
        return -1; 
    } 

    n = 0;
    while (1) {
			buffer[0] = 0x02;  /* command byte: write output regs */ 
			buffer[1] = 0x00;  /* port0 data  */
      if (write(fd, buffer, 2) != 2) { 
         printf("write error 0"); 
         return -1; 
      } 
      usleep(100000);
			buffer[0] = 0x02;  /* command byte: write output regs */ 
			buffer[1] = 0xff;  /* port0 data  */
      if (write(fd, buffer, 2) != 2) { 
         printf("write error 1"); 
         return -1; 
      } 
			printf("%d\n", n++);
      usleep(100000);
    }
}
