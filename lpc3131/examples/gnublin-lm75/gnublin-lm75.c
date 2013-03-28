
#include <stdio.h>
#include <stdlib.h>
#include <fcntl.h>
#include <linux/i2c.h>
#include <linux/i2c-dev.h>
#include <unistd.h>
#include <getopt.h>


#define ADDR 0x48

int c,hflag;
char *filename = "/dev/i2c-1";
int slave_address = ADDR;
int json_flag = 0;
int brute_flag = 0;


void parse_opts(int argc, char **argv)
{

	while((c = getopt(argc,argv,"hjb")) != -1)
	{
		switch(c)
		{
			case 'h' : hflag = 1;		break;	/* help */
			case 'j' : json_flag = 1;	break;
			case 'b' : brute_flag = 1;	break;
		}

	}
	if (hflag)
	{
		printf("This program is designed, to easily read the temperature from a LM75 Temp Sensor connected to the GNUBLIN.\n\n-h Show this help\n-j Convert output to json format.\n-b show output in raw format\n\r");
	exit(1);

	}
}


int main (int argc, char **argv) {
	int fd;
    unsigned char rx_buf[128];
    unsigned int n;
	short value=0;
    float temp;

    parse_opts(argc, argv);


    if (argc == 0) {
	 printf("This program is designed, to easily read the temperature from a LM75 Temp Sensor connected to the GNUBLIN.\n\n-h Show this help\n-j Convert output to json format.\n-b show output in raw format\n\r");
	 exit(1);
	}





    if ((fd = open(filename, O_RDWR)) < 0) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
      else
        printf("Failed to open i2c device \n");
        return -1;
    }

    if (ioctl(fd, I2C_SLAVE, slave_address) < 0) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
      else
        printf("ioctl I2C_SLAVE error \n");
        return -1;
    }


	if (read(fd, rx_buf, 2) != 2){
		if (json_flag == 1)
		  printf("{\"error_msg\" : \"Failed to read from i2c device \",\"result\" : \"-1\"}\n");
		else
		  printf ("ERROR I2C read \n");
		  return -1;
	}

	// rx_buf[0] = MSByte
	// rx_buf[1] = LSByte
	// save the MSB
	value = rx_buf[0];
	// make space for the LSB
	value<<=8;
	// save the LSB
	value |= rx_buf[1];
	// Bit 0-4 isn't used in the LM75, so shift right 5 times
	value>>=5;

	//check if temperature is negative
	if(rx_buf[0] & 0x80)
	{
		value = value|0xF800;
		value =~value +1;
		temp = value*0.125;
		if (json_flag == 1)
        	  printf("{\"temperature\" : \"-%.3f\",\"result\" : \"0\"}\n", temp);
		else if (brute_flag ==1)
		  printf("-%.3f \n", temp);
      		else
		  printf("-%.3f °C \n\r", temp);
	}
	else {
		temp = value*0.125;
		if (json_flag == 1)
                  printf("{\"temperature\" : \"%.3f\",\"result\" : \"0\"}\n", temp);
		else if (brute_flag ==1)
                  printf("-%.3f \n", temp);

                else
		printf("%.3f °C \n\r", temp);
	}

close(fd);
}
