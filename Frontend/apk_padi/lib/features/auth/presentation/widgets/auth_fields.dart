import 'package:flutter/material.dart';

class PadiTextField extends StatelessWidget {
  const PadiTextField({
    super.key,
    required this.controller,
    required this.label,
    this.keyboardType,
    this.textInputAction,
    this.obscureText = false,
    this.errorText,
    this.prefixIcon,
    this.suffixIcon,
    this.decoration,
  });

  final TextEditingController controller;
  final String label;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final bool obscureText;
  final String? errorText;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final InputDecoration? decoration;

  @override
  Widget build(BuildContext context) {
    final defaultDecoration = InputDecoration(
      labelText: label,
      prefixIcon: prefixIcon == null ? null : Icon(prefixIcon),
      suffixIcon: suffixIcon,
    );

    final effectiveDecoration = (decoration ?? defaultDecoration).copyWith(
      labelText: decoration?.labelText ?? label,
      errorText: errorText,
      prefixIcon: decoration?.prefixIcon ??
          (decoration == null && prefixIcon != null ? Icon(prefixIcon) : null),
      suffixIcon: decoration?.suffixIcon ?? (decoration == null ? suffixIcon : null),
    );

    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      textInputAction: textInputAction,
      obscureText: obscureText,
      decoration: effectiveDecoration,
    );
  }
}

class ErrorBanner extends StatelessWidget {
  const ErrorBanner({super.key, required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    if (message.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF1E8),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Text(
        message,
        style: const TextStyle(
          color: Color(0xFF9A3412),
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
