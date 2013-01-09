
#include <stdio.h> 
#include <stdlib.h>
#include <fcntl.h> 
#include <linux/i2c.h>
#include <linux/i2c-dev.h>
#include <unistd.h>
#include <getopt.h>


#define ADDR 0x60

unsigned int position = 0;
int c,hflag;
char *filename = "/dev/i2c-1"; 
int slave_address = ADDR; 



void parse_opts(int argc, char **argv)
{	
	
	while((c = getopt(argc,argv,"hp:f:a:")) != -1)
	{
		switch(c)
		{
			case 'h' : hflag = 1;                               break;				/* help */
			case 'p' : position = atoi(optarg);                 break;
			case 'f' : filename = optarg;                       break;
			case 'a' : slave_address = strtol (optarg,NULL,16); break;
		}

	}
	if (hflag)
	{
		printf("Usage: gnublin-step -f \"DEVICE\" -a I2C-address -p POSITION \n\nIf you dont specify an I2Caddress, the program will use the default address 0x60 \n\nIf you dont specify a device, the program will use the default I2C-Device /dev/i2c-1 \n\nAn examplecall can look like this: ./i2c-step -a 0x60 -p 3000");		
	exit(1);
		
	}
}


int main (int argc, char **argv) { 

    int fd; 
    unsigned char buffer[128];
    unsigned char rx_buf[128];
    unsigned int n, err,test; 
    
    parse_opts(argc, argv);
    
    
    if (argc == 0) {
        printf("Usage: gnublin-step -f \"DEVICE\" -a I2C-address -p POSITION \n\nIf you dont specify an I2Caddress, the program will use the default address 0x60 \n\nIf you dont specify a device, the program will use the default I2C-Device /dev/i2c-1 \n\nAn examplecall can look like this: ./i2c-step -a 0x60 -p 3000");
		    //exit(1);
		}


    printf("device = %s\n", filename);



    if ((fd = open(filename, O_RDWR)) < 0) { 
        printf("i2c open error"); 
        return -1; 
    } 

    if (ioctl(fd, I2C_SLAVE, slave_address) < 0) { 
        printf("ioctl I2C_SLAVE error"); 
        return -1; 
    } 


    
      //GestFullStatus1 Command: This Command must be executed before Operating
      buffer[0] = 0x81;   

      if (write(fd, buffer, 1) != 1) { 
         printf("write error 0\n"); 
         return -1; 
      } 
     
     sleep(1);
     
    
      //RunInit command:  This Command must be executed before Operating
      buffer[0] = 0x88; 

      if (write(fd, buffer, 1) != 1) { 
         printf("write error 1\n"); 
         return -1; 
      } 



      printf("Position is: %i\n", position); //Print the Position
        
      
//SetPosition
buffer[0] = 0x8B;   // SetPosition Command
buffer[1] = 0xff;   // not avialable
buffer[2] = 0xff;   // not avialable 
buffer[3] = (unsigned char) (position >> 8);  // PositionByte1 (15:8)
buffer[4] = (unsigned char)  position;       // PositionByte2 (7:0)


      if (write(fd, buffer, 5) != 5) { 
         printf("write error 2\n"); 
         return -1; 
      } 


printf("Step done.\n");


close(fd);
}
