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
int json_flag = 0;
int brute_flag = 0;
unsigned int irun = 15;
unsigned int ihold = 1;
unsigned int vmax = 8;
unsigned int vmin = 0;
int new_address = -1;

int newAddress(int);

void parse_opts(int argc, char **argv)
{

        while((c = getopt(argc,argv,"hjp:f:a:bi:d:x:n:o:")) != -1)
        {
                switch(c)
                {
                        case 'h' : hflag = 1;                               break;                              /* help */
                        case 'p' : position = atoi(optarg);                 break;
                        case 'f' : filename = optarg;                       break;
                        case 'a' : slave_address = strtol (optarg,NULL,16); break;
                        case 'j' : json_flag = 1;                           break;
                        case 'b' : brute_flag = 1;                          break;
			case 'i' : irun = atoi(optarg);			    break;
			case 'd' : ihold = atoi(optarg);		    break;
			case 'x' : vmax = atoi(optarg);			    break;
			case 'n' : vmin = atoi(optarg);			    break;
			case 'o' : new_address = strtol (optarg,NULL,16);   break;
		}

        }
        if (hflag | argc == 1)
        {
                printf("This program is designed, to easily interact with a stepper-motor connected to the GNUBLIN.\n\n-h Show this help\n-f <device> Specify the i2c-device.default=/dev/i2c-1\n-j Convert output to json format.\n-b show output in raw format.\n-a <I2C-address> Specify the stepper modules I2C-address.default=0x60\n-p <Position> Specify the desired position\n-i <Irun> Specify the Irun parameter (0-15)\n-d <Ihold> Specify the iholD parameter (0-15)\n-x <vmax> Specify the vmaX parameter (0-15)\n-n <vmin> Specify the vmiN parameter (0-15)\n-o <new_address> sets the new I2C Address to the controller\n\nExamples:\n\nDrive the motor to position 3000 and use I2C-address 0x60:\ngnublin-step -a 0x60 -p 3000\n\nA complete rotation is position 3200, two rotations 6400 and so on.\n");              
        	exit(1);
        }
}

int newAddress(int fd){

  //SetOTPParam
  unsigned char buffer[128];
  int new_ad = 0;
  int old_ad = 0;
  char yes[3] = "NO!";

 if(new_address <= slave_address){
	printf("\tThe new address must be higher than the old one (0x%x)!\n",slave_address);
	exit(1);
 }
 else if (new_address > 0x7f){
	printf("The biggest slave address of the TMC222 is 0x7f\n");
 }
 else{
  old_ad = (slave_address & 0b0011110) >> 1;
  new_ad = (new_address & 0b0011110) >> 1;
  if((new_ad & 0b0001)<(old_ad & 0b0001)|(new_ad & 0b0010)<(old_ad & 0b0010)|(new_ad & 0b0100)<(old_ad & 0b0100)|(new_ad & 0b1000)<(old_ad & 0b1000)){
        printf("\tThis address could not be set, because the '1' cant be undone!\n \told OTP AD: 0x%x\n\tnew OTP AD: 0x%x\n",old_ad, new_ad);
        exit(1);
  }
  if((new_address & 0b00000001) == 1){
	printf("\tThe LSB address bit is set by the jumper on the module-step\n");
	new_address --;
	printf("\tThe new address will be set to: 0x%x \n", new_address);
  }

    printf("\tIf a bit of the OTP (on time programmable) is set, it cant be undone! \n\tIf you are sure to write the new Address (0x%x) then type 'yes' in CAPITALS\n\n\t", new_address);
    scanf("%s", yes);
    if(strcmp(yes, "YES") == 0){
 	buffer[0] = 0x90; //SetOTPParam
  	buffer[1] = 0xff; //N/A
  	buffer[2] = 0xff; //N/A
  	buffer[3] = 0x02; //set AD3 AD2 AD1 AD0
  	buffer[4] = (unsigned char) new_ad;

      	if (write(fd, buffer, 6) != 6) {
                printf("ERROR: No module is connected on address 0x%x\n",slave_address);
         	return -1;
      	}

	printf("\tNew Address was successfully set to: 0x%x\n\n", new_address);
	exit(1);
    }

  else{
	printf("\tYou didn't type 'YES'\n");
	exit(1);
  } 
}
}

void readTMC(int num, int fd, char rx_buf[128]){
    int i =0;
    for (i; i < num; i++){
       rx_buf[i] = 0;
    }
    i = 0;
    if (read(fd, rx_buf, num) != num)
        printf("Read Error");
    else{
   	 for(i; i < num; i++){
		printf("RX %i: %x \n", i, rx_buf[i]);
    	 }
    }
    printf("\n");
}


int main (int argc, char **argv) {

    int fd;
    unsigned char buffer[128];
    unsigned char rx_buf[128];
    unsigned int n, err,test;

    parse_opts(argc, argv);


    if ((fd = open(filename, O_RDWR)) < 0) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
      else
        printf("Failed to open i2c device");
        return -1;
    }

    if (ioctl(fd, I2C_SLAVE, slave_address) < 0) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
      else
        printf("ioctl I2C_SLAVE error");
        return -1;
    }

    if(new_address != -1)
        {
                newAddress(fd);
		exit(1);
        }


      //SetMotorParam
  buffer[0] = 0x89; //SetMotorParam
  buffer[1] = 0xff; //N/A
  buffer[2] = 0xff; //N/A
  buffer[3] = (unsigned char) ((irun * 0x10) + ihold); //Irun & I hold
  buffer[4] = (unsigned char) ((vmax * 0x10) + vmin); //Vmax & Vmin 
  buffer[5] = 0x00; //status
  buffer[6] = 0x00; //securePos
  buffer[7] = 0x00; //StepMode

      if (write(fd, buffer, 8) != 8) {
      if (json_flag == 1)
                 printf("{\"error_msg\" : \"no module is connected on address 0x%x\",\"result\" : \"-4\"}\n",slave_address);
        else
               	 printf("ERROR: No module is connected on address 0x%x\n",slave_address);
	         return -1;
      }


      //GetFullStatus1 Command: This Command must be executed before Operating
      buffer[0] = 0x81;

      if (write(fd, buffer, 1) != 1) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"GetFullStatus1 error\",\"result\" : \"-2\"}\n");
      else
         printf("GetFullStatus1 error\n");
         return -1;
      }



      //RunInit command:  This Command must be executed before Operating
      buffer[0] = 0x88;

      if (write(fd, buffer, 1) != 1) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"RunInit error\",\"result\" : \"-3\"}\n");
      else
         printf("RunInit error\n");
         return -1;
      }


//SetPosition
buffer[0] = 0x8B;   // SetPosition Command
buffer[1] = 0xff;   // not avialable
buffer[2] = 0xff;   // not avialable
buffer[3] = (unsigned char) (position >> 8);  // PositionByte1 (15:8)
buffer[4] = (unsigned char)  position;       // PositionByte2 (7:0)


      if (write(fd, buffer, 5) != 5) {
      if (json_flag == 1)
          printf("{\"error_msg\" : \"SetPosition error\",\"result\" : \"-4\"}\n");
      else
         printf("SetPosition error\n");
         return -1;
      }

      if (json_flag == 1)
          printf("{\"result\" : \"0\"}\n");
      else
          printf("Step done.\n");


close(fd);
}

