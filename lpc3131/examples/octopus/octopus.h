/***************************************************************************
                          octopus.h  -  description
                             -------------------
    begin                : Fri Apr 1 2007
    copyright            : (C) 2007 by Embedded Projects
    email                : sauter@embedded-projects.net
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License           *
 *   version 2.1 as published by the Free Software Foundation;             *
 *                                                                         *
 ***************************************************************************/

#ifndef __liboctopus_h__
#define __liboctopus_h__

#include "usb.h"


#define VID 0x1781
#define PID 0x0c65

/**
  \brief Main context structure for all liboctopus functions.

 Do not access directly if possible.
*/
struct octopus_context {
  // USB specific
  /// libusb's usb_dev_handle
  struct usb_dev_handle *usb_handle;

  /// String representation of last error
  char *error_str;
};

/**
    \brief list of usb devices created by octopus_usb_find_all()
*/
struct octopus_device_list {
  /// pointer to next entry
  struct octopus_device_list *next;
  /// pointer to libusb's usb_device
  struct usb_device *dev;
};

#ifdef __cplusplus
extern "C" {
#endif

  // generic
  int octopus_init(struct octopus_context *octopus);

  int octopus_open(struct octopus_context *octopus);
  int octopus_open_dev(struct octopus_context *octopus, struct usb_device *dev);
  int octopus_open_id(struct octopus_context *octopus, int vendor, int product);
  int octopus_open_serial(struct octopus_context *octopus, char * serial);

  int octopus_close(struct octopus_context *octopus);

  int octopus_message(struct octopus_context *octopus, unsigned char *msg,
           unsigned int msglen, unsigned char *answer, unsigned int answerlen);

  char * octopus_get_hwdesc(struct octopus_context *octopus, char *desc);

  // IO
  int octopus_io_init(struct octopus_context *octopus, unsigned int pin);
  int octopus_io_init_port(struct octopus_context *octopus, unsigned int port);

  int octopus_io_set_port_direction_out(struct octopus_context *octopus, 
                                        unsigned int port,unsigned char mask);
  int octopus_io_set_port_direction_in(struct octopus_context *octopus, 
                                        unsigned int port, unsigned char mask);
  int octopus_io_set_port_direction_tri(struct octopus_context *octopus, 
                                        unsigned int port, unsigned char mask);

  int octopus_io_set_pin_direction_out(struct octopus_context *octopus, unsigned int pin);
  int octopus_io_set_pin_direction_in(struct octopus_context *octopus, unsigned int pin);
  //int octopus_io_set_pin_direction_in_pullup(struct octopus_context *octopus, int pin);
  int octopus_io_set_pin_direction_tri(struct octopus_context *octopus, unsigned int pin);

  unsigned char octopus_io_get_port(struct octopus_context *octopus, unsigned int port);

  int octopus_io_set_port(struct octopus_context *octopus, unsigned int port, unsigned char value);

  int octopus_io_set_pin(struct octopus_context *octopus, unsigned int pin, unsigned int value);	//buffered

  int octopus_io_get_pin(struct octopus_context *octopus, unsigned int pin);

  /// part: adc

  int octopus_adc_init(struct octopus_context *octopus, unsigned int pin);
  int octopus_adc_get(struct octopus_context *octopus, unsigned int pin);
  int octopus_adc_ref(struct octopus_context *octopus, unsigned int ref); 
  // 1= extern AREF, 2 = AVCC as reference, 3=intern voltage


  /// part: I2C
  int octopus_i2c_init(struct octopus_context *octopus);
  int octopus_i2c_deinit(struct octopus_context *octopus);

  int octopus_i2c_send_defaults(struct octopus_context *octopus, int scl_freq, char address, char *data, int length);
  int octopus_i2c_set_bitrate(struct octopus_context *octopus, int bitrate);
  int octopus_i2c_send_byte(struct octopus_context *octopus, char data);
  int octopus_i2c_send_bytes(struct octopus_context *octopus, char *data, int length);

  int octopus_i2c_send_start(struct octopus_context *octopus);
  int octopus_i2c_send_stop(struct octopus_context *octopus);
  char octopus_i2c_receive_byte(struct octopus_context *octopus, int address);
  int octopus_i2c_recv(struct octopus_context *octopus, int address, char *buf, int length);

   /// part: UART
  int octopus_uart_init(struct octopus_context *octopus, int uartport);
  int octopus_uart_init_default(struct octopus_context *octopus, int uartport);
  int octopus_uart_init_defaults(struct octopus_context *octopus, int uartport, unsigned long int baudrate, int databits, char parity, int stopbits);
  int octopus_uart_deinit(struct octopus_context *octopus, int uartport);

  int octopus_uart_stopbits(struct octopus_context *octopus, int uartport, int stopbits);
  int octopus_uart_databits(struct octopus_context *octopus, int uartport, int databits);
  int octopus_uart_baudrate(struct octopus_context *octopus, int uartport, unsigned long int baudrate);
  int octopus_uart_parity(struct octopus_context *octopus, int uartport, char parity);

  int octopus_uart_send(struct octopus_context *octopus, int uartport, char *data, int length);

  /// part: SPI
  int octopus_spi_init(struct octopus_context *octopus, int dord, int mode, int speed);
  int octopus_spi_deinit(struct octopus_context *octopus);

  int octopus_spi_send(struct octopus_context *octopus, unsigned char * buf, int length);
  int octopus_spi_recv(struct octopus_context *octopus, unsigned char * buf, int length);
  int octopus_spi_send_and_recv(struct octopus_context *octopus, unsigned char * buf, int length);

  /// part:  Flash 93c46
  int octopus_93c46_init(struct octopus_context *octopus);
  int octopus_93c46_deinit(struct octopus_context *octopus);

  int octopus_93c46_read(struct octopus_context *octopus, unsigned char address, int length, unsigned char * buf);
  int octopus_93c46_write(struct octopus_context *octopus, unsigned char address, int length, unsigned char * buf);

  /// part:  PWM
  int octopus_pwm_init(struct octopus_context *octopus, int pin);
  int octopus_pwm_deinit(struct octopus_context *octopus, int pin);
  int octopus_pwm_speed(struct octopus_context *octopus, int pin, int speed);
  int octopus_pwm_value(struct octopus_context *octopus, int pin, unsigned char value);

  // part: CAN
  int octopus_can_deinit(struct octopus_context *octopus);
  int octopus_can_send_remote(struct octopus_context *octopus, unsigned int mob);
  int octopus_can_receive_data(struct octopus_context *octopus, unsigned int mob, unsigned int *id, char *buf);
  int octopus_can_init(struct octopus_context *octopus, unsigned int baudrate, unsigned int eid);
  int octopus_can_enable_mob(struct octopus_context *octopus, unsigned int mob, unsigned int mode, unsigned int id, unsigned int idm);
  int octopus_can_send_data(struct octopus_context *octopus, unsigned int mob, unsigned int length, char *data);
  int octopus_can_disable_mob(struct octopus_context *octopus, unsigned int mob);
  int octopus_can_set_autoreply(struct octopus_context *octopus, unsigned int mob, unsigned int length, char *data);
   

   //eeprom
  int octopus_eeprom_write_bytes(struct octopus_context *octopus, unsigned int addr, char *buf, unsigned int length);
  int octopus_eeprom_read_bytes(struct octopus_context *octopus, unsigned int addr, char *buf, unsigned int length);

#ifdef __cplusplus
}
#endif

#endif /* __liboctopus_h__ */
