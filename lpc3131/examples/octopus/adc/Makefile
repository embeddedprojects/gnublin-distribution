CC = arm-linux-gnueabi-gcc

all:
	$(CC) -Wall -I.. -g -c adc_led.c 
	$(CC) -Wall -g -o adc_led adc_led.o -L../libs -lusb -loctopus

clean:
	rm -f *.o *~ adc_led

