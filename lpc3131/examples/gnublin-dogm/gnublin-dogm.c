/*
 * Author: Benedikt Niedermayr
 * 
 * Display_controll v1.0
 *
 * Description:
 * This is an application to controll an DOGM162x-A LCD-Display with ST7036 controller.
 * 
 * Before starting this application please export the GPIO_PIN (e.g. export GPIO_PIN=gpio11)
 * environment variable correspond with you choosen gpio pin on your
 * board(RS_PIN at display).
 * 
 */

#include <stdint.h>
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <getopt.h>
#include <fcntl.h>
#include <sys/ioctl.h>
#include <linux/types.h>
#include <linux/spi/spidev.h>
#include <string.h>

#define ARRAY_SIZE(a) (sizeof(a) / sizeof((a)[0]))
#define BUFF_SIZE 64
#define DISPLAY_LINE_1 128
#define DISPLAY_LINE_2 192

#define CURS_AUTO_DEC 0x04

 
/* DEBUGGING */
//#define DEBUG 
 

static void pabort(const char *s)
{
	printf("%s",s);
	exit(1);
}

char *device_file = "/dev/spidev0.0";
char *string_display = "Default String";
long int write_speed = 1;
int fd;
int shift_val, c, cursor_offset, curs_auto_dec;
char *pinnumber="14";
int hflag, modeflag, cursorflag, displayflag, shiftflag, initflag = 0;
static uint8_t mode;
static uint8_t bits = 8;
static uint32_t speed = 100000;
static uint16_t delay;
int json_flag = 0;

/* This string contains the command for initializing the display */
uint8_t const init_command[BUFF_SIZE] = {
		 0x39, 0x14, 0x55, 0x6D, 0x78, 0x38, 0x0F, 0x01, 0x06, 
};

/* This string contains the real data to be sent! */
uint8_t tx_real[BUFF_SIZE]={0,};





/*
 * Clear a string 
 */ 
void clearstring(uint8_t *s)
{
	int i = 0;
	while(i<BUFF_SIZE)
	{
		s[i]='\0';
		i++;
	}
}




/*
 * This function copies the tx_real buffer part for part into the tx buffer
 * and sends it via SPI
 * The tx_real buffer should be filled before calling this function
 */
static void transfer(int fd)
{
	int ret;
	int cnt_down,index_array;
	uint8_t tx[] = {
		0x39,0x00,
	};
	uint8_t rx[ARRAY_SIZE(tx)] = {0, };
	struct spi_ioc_transfer tr = {
		.tx_buf = (unsigned long)tx,
		.rx_buf = (unsigned long)rx,
		.len = 1, //ARRAY_SIZE(tx),
		.delay_usecs = delay,
		.speed_hz = speed,
		.bits_per_word = bits,
	};
	
#ifdef DEBUG
	printf("transfer called\n");
	printf("\n");
#endif

	cnt_down = strlen(&tx_real[0]);
	index_array=0;

#ifdef DEBUG	
	printf("tx_real has %d elements\n",cnt_down);
#endif
	
	while(cnt_down)
	{
		tx[0] = tx_real[index_array];	
		ret = ioctl(fd, SPI_IOC_MESSAGE(1), &tr); /* SEND via SPI*/
		cnt_down--;
		index_array++;
	
		usleep(write_speed);
	}
	
	if (ret == -1)
		if (json_flag == 1)
		printf("{\"error_msg\" : \"can't send spi message\",\"result\" : \"-11\"}\n");
		else
		printf("can't send spi message\n");
	
	clearstring(&tx_real[0]);

#ifdef DEBUG	
	printf("transfer closed\n");
	printf("\n");
#endif

}

void parse_opts(int argc, char **argv)
{	
	
	while((c = getopt(argc,argv,"amnjhw:l:d:o:s:i:t")) != -1)
	{
		switch(c)
		{
			case 'h' : hflag = 1;                               break;				/* help */
			case 'w' : string_display = optarg; displayflag = 1;break; 
			case 'd' : device_file =    optarg;                 break;
			case 'o' : cursor_offset = atoi(optarg); cursorflag = 1;break;
			case 'm' : modeflag = 1;                            break;
			case 'n' : initflag = 1;                          break;
			case 's' : shift_val = atoi(optarg);shiftflag = 1;  break;
			case 'a' : curs_auto_dec = 1;                       break;
			case 't' : write_speed = 160000;              		break;
			case 'j' : json_flag = 1;                           break;
			case 'i' : pinnumber =  optarg;						break;
		}

	}
	if (hflag)
	{
		printf("Usage: %s [-wdhnso]\n", argv[0]);		
		puts("  -d            device to use (default /dev/spidev0.0)\n"
	     "  -w            write string to display\n"
	     "  -d            specify a device file\n"
	     "  -j            Convert Output to json Format\n"
	     "  -o            Set cursor to position(Start line 1 = 128\n"
		 "                                       Start line 2 = 192)\n"
		 "                                                          \n"
	     "  -n            reset the display.                        \n"
		 "  -s[+/-x]      shift display [x] times(left shift = -    \n"
		 "                                        right shift= +    \n"
         "                                                          \n"
		 "  -a            Change auto increment of cursor           \n"
		 "                to auto decrement(for this command)       \n"
		 "  -t            Slow down the write speed					\n"
		 "  -i 			  Use GPIO Pin x instead default GPIO Pin 14\n"
		 "				  (For RS Pin on DOGM Display				\n"
		 "All operations except [-w -o -s] and [-o -s] are allowed\n");
	exit(1);
		
	}

#ifdef DEBUG	
	/* debugging */
	printf("string_display:%s\n",string_display);
	//printf("string_line:%s\n",string_line);
	printf("devie_file:%s\n",device_file);
	printf("cursor_offset:%d \n",cursor_offset);
#endif	
}


/*
 * The Icons wich where displayed are liable to the ASCII code.
 * The string will be easily be converted from char to ASCII and
 * ASCII correspond with the command wich will be send.
 * E.g the char 0 is in ASCII 0x30, so the command for 0 is also
 * 0x30
 * This functions fills the tx_real buffer from string_display buffer!!
 */
int fill_tx_real(void)
{	
	
	char temp;
	int  x;

#ifdef DEBUG	
	printf("\n");	
	printf("fill_tx_real called\n");
	printf("String %s\n",string_display);
#endif
	
	
	clearstring(&tx_real[0]);

	/* Kopieren und umwandeln des übergebenen Strings in den tx_real String */	
	x = 0;
	while( string_display[x] != '\0')
	{	
		tx_real[x] = string_display[x];
#ifdef DEBUG
		printf("tx_real[%d] is %x\n", x, tx_real[x]);
#endif		
		x++;
	}
	

#ifdef DEBUG	
	printf("tx_real (char)-->%s\n",tx_real);
	printf("fill_tx_real closed\n");
	printf("\n");
#endif
	
transfer(fd);
return 0;

}


/*
 * This function initalizes the display
 * The init_command string will be copied into tx_real buffer
 */
void init_display(void)
{
	int x = 0;	
	clearstring(&tx_real[0]);	
	while(init_command[x] != '\0')
	{
		tx_real[x] = init_command[x];
		x++;
	}
transfer(fd);
}



/*
 * Testfunction
 */
int test(int val)
{	
	int x = 0;	
	
	/* specify the command which will be send */
	uint8_t const tmp_string[10] = {
			0x04,
	};
	
	clearstring(&tx_real[0]);
	if(val)
	{
		tx_real[0] = 0x04;		
	}else{
		tx_real[0] = 0x06;	
	}	
	
	transfer(fd);
	return 0;
}


/*
 * This function takes cursor_offset as parameter for positioning
 * the cursor on the display.
 * First line is from 128-143
 * Second line is from 192-207
 * Its possible to overwrite the display and then shifting the symbols
 * into the display.
 */
int set_cursor(uint8_t offs)
{	

#ifdef DEBUG		
	printf("\n");
	printf("set_cursor called\n");
#endif
	

	clearstring(&tx_real[0]);
	tx_real[0] = offs;
		
#ifdef DEBUG	
	printf("tx_real -->%x\n", tx_real[0]);
	printf("\n");
	printf("set_cursor closed\n");
#endif	

transfer(fd);
return 0;
}

/*
 * Shift the display.
 * It takes the shift_val parameter.
 * shift_val < 0 --> left shift
 * shift_val > 0 --> right shift
 */
int shift(int s_val)
{
	int x,cnt = 0;

#ifdef DEBUG	
	printf("\n");
	printf("shift called with %d\n",s_val);
#endif	

	clearstring(&tx_real[0]);
	
	if(shift_val == 0)
	{
		if (json_flag == 1)
		printf("{\"error_msg\" : \"shift value 0 is not allowed!\",\"result\" : \"-10\"}\n");
		else
		printf("shift value 0 is not allowed!\n");
		return -1;
	}	

	if(shift_val <= 0)
	{	
		x=s_val;
		while(x < 0)
		{
			tx_real[cnt] = 0x18;
#ifdef DEBUG
			printf("tx_real-->%x\n", tx_real[cnt]);
#endif			
	    	x++;
			cnt++;
		}
	}
	
	if(shift_val >= 0)
	{	
		x=s_val;
		while(x > 0)
		{		
			tx_real[cnt] = 0x1C;
#ifdef DEBUG
			printf("tx_real-->%x\n", tx_real[cnt]);
#endif
			x--;
			cnt++;
		}
	}

#ifdef DEBUG
	printf("shift closed\n");
	printf("\n");
#endif

	transfer(fd);
}



/* BEGINN MAIN */
int main(int argc, char **argv)
{
	int ret = 0;
	

	parse_opts(argc, argv);
	
	setenv("GPIO_PIN",pinnumber,1);
	
//	if (json_flag != 1)
//	system("echo $GPIO_PIN");
	
	system("echo $GPIO_PIN > /sys/class/gpio/export");
	system("echo low > /sys/class/gpio/gpio$GPIO_PIN/direction");
	
	fd = open(device_file, O_RDWR);
	if (fd < 0)	        
		if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't open device\",\"result\" : \"-1\"}\n");
		else
		pabort("can't open device");

	/*
	 * spi mode
	 */
	ret = ioctl(fd, SPI_IOC_WR_MODE, &mode);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't set spi mode\",\"result\" : \"-2\"}\n");
		else
		pabort("can't set spi mode");

	ret = ioctl(fd, SPI_IOC_RD_MODE, &mode);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't get spi mode\",\"result\" : \"-3\"}\n");
		else
		pabort("can't get spi mode");

	/*
	 * bits per word
	 */
	ret = ioctl(fd, SPI_IOC_WR_BITS_PER_WORD, &bits);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't set bits per word\",\"result\" : \"-4\"}\n");
		else
		pabort("can't set bits per word");

	ret = ioctl(fd, SPI_IOC_RD_BITS_PER_WORD, &bits);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't get bits per word\",\"result\" : \"-5\"}\n");
		else
		pabort("can't get bits per word");

	/*
	 * max speed hz
	 */
	ret = ioctl(fd, SPI_IOC_WR_MAX_SPEED_HZ, &speed);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't set max speed hz\",\"result\" : \"-6\"}\n");
		else
		pabort("can't set max speed hz");

	ret = ioctl(fd, SPI_IOC_RD_MAX_SPEED_HZ, &speed);
	if (ret == -1)
	        if (json_flag == 1)
		pabort("{\"error_msg\" : \"can't get max speed hz\",\"result\" : \"-7\"}\n");
		else
		pabort("can't get max speed hz");

	if (json_flag == 1)
	printf("{\"spi mode\" : \"%d\",\"bits per word\" : \"%d\",\"max speed\" : \"%d Hz (%d KHz)\",\"result\" : \"0\"}\n,",mode, bits, speed, speed/1000);
	else
	{
	printf("spi mode: %d\n", mode);
	printf("bits per word: %d\n", bits);
	printf("max speed: %d Hz (%d KHz)\n", speed, speed/1000);
	}
	
	/* Do not init the display, maybe you only want to make a shift operation ? */
	if(initflag)
	{	
		/* Display initialisation */			
		if((system("echo 0 >/sys/class/gpio/gpio$GPIO_PIN/value")) != 0)
		{		
		  	if (json_flag == 1)
			printf("{\"error_msg\" : \"export GPIO_PIN=gpio[x] may help\",\"result\" : \"-8\"}\n");
			else
			printf("export GPIO_PIN=gpio[x] may help\n");
			return -1;
		}
		//system("echo $RS_GPIO_PIN");
		init_display();
	}	

	/* Set auto decrement/increment of cursor */
	test(curs_auto_dec);
	

	if(modeflag)
	{
		if (json_flag == 1)
		printf("{\"error_msg\" : \"Display shift mode not implemented yet!\",\"result\" : \"-9\"}\n");
		else
		printf("Display shift mode not implemented yet!\n");
	}
	else {

		/* Copy only one string or char onto the display --> Standard */
		if(displayflag & !cursorflag & !shiftflag) 
		{
			
	
			/* Make ready for Data transfer */
			system("echo 1 >/sys/class/gpio/gpio$GPIO_PIN/value"); 
			fill_tx_real();
			
			system("echo 0 >/sys/class/gpio/gpio$GPIO_PIN/value");
		}

		/* Only set cursor to position cursor_offset */		
		if(cursorflag & !displayflag & !shiftflag) 		
		{
			if (json_flag == 1)
			printf("{\"Cursor_offset\" : \"%d\",\"result\" : \"0\"}\n",cursor_offset);
			else
			printf("Cursor_offset-->%d",cursor_offset);			
			set_cursor(cursor_offset);
			
		
		}

		/* Write string at defined cursor position */
		if(displayflag & cursorflag & !shiftflag)
		{
			set_cursor(cursor_offset);
			
			
			/* Make ready for Data transfer */
			system("echo 1 >/sys/class/gpio/gpio$GPIO_PIN/value"); 
			fill_tx_real();
			
			system("echo 0 >/sys/class/gpio/gpio$GPIO_PIN/value");
		}		
		
		/* Only perform shift operation defined with shift_val */		
		if(!displayflag & !cursorflag & shiftflag)
		{
			shift(shift_val);
			//test();
			
		}
		
		/* First write string to display, then shift display */
		if(displayflag & shiftflag & !cursorflag)
		{
			/* Make ready for Data transfer */
			system("echo 1 >/sys/class/gpio/gpio$GPIO_PIN/value"); 
			fill_tx_real();
			
			system("echo 0 >/sys/class/gpio/gpio$GPIO_PIN/value");

			usleep(500000);
			shift(shift_val);
			
		}
	
	}
	
	
	close(fd);
	return ret;
}
