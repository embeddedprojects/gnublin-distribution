CC = arm-linux-gnueabi-gcc

all:
	$(CC) -Wall -I.. -g -c led.c 
	$(CC) -Wall -g -o led led.o ../libs/libusb.a ../libs/liboctopus.a

clean:
	rm -f *.o led *~

