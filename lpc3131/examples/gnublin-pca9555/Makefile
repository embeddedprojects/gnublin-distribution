#include src/Makefile
CC = arm-linux-gnueabi-gcc
#CC = gcc
#CFLAGS = -I /opt/eldk-5.2.1/armv5te/sysroots/armv5te-linux-gnueabi/usr/include/linux/ 
TARGET = gnublin-pca9555
#TARGET = i2c_dev
#TARGET = pca9555
SOURCES = i2c_dev.c pca9555.c

OBJ =  $(SOURCES:%.c=%.o) $(TARGET:%=%.o)

all: $(OBJ)
	$(CC) -o $(TARGET) $(OBJ) $(CFLAGS)

clean:
	rm $(OBJ) -f $(TARGET)

install:
	make
	scp $(TARGET) gnublin@192.168.1.24:/home/gnublin
