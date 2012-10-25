
# compile for ARM with ELDK-5.0
# ARCH = arm
# CROSS_COMPILE = arm-linux-gnueabi-
# H. Hoegl, Sep 2011

obj-m += hello.o

BLDPATH = /home/hhoegl/work/eplpc3131/kernel/install/lib/modules/2.6.33/build
# BLDPATH = /lib/modules/$(shell uname -r)/build

all:
	make -C $(BLDPATH) M=$(PWD) modules

clean:
	make -C $(BLDPATH) M=$(PWD) clean
