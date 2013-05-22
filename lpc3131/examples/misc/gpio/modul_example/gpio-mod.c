/*
   Switch red LED (GPIO3) on and off.
*/ 

#include <linux/module.h>
#include <asm/io.h>

#include <mach/hardware.h>
#include <mach/gpio.h>


int init_module(void)
{
    printk("gpio-mod: init\n");
    gpio_direction_output(GPIO_GPIO3, 1);
    return 0;

}

void cleanup_module(void)
{
    printk("gpio-mod: cleanup\n");
    gpio_direction_output(GPIO_GPIO3, 0);
}


/* vim: set et ts=4 sw=4: */
