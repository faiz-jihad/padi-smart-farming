import 'package:padi/core/storage/token_storage.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('InMemoryTokenStorage menyimpan dan menghapus token', () async {
    final storage = InMemoryTokenStorage();

    expect(await storage.readToken(), isNull);

    await storage.saveToken('plain-text-token');
    expect(await storage.readToken(), 'plain-text-token');

    await storage.clearToken();
    expect(await storage.readToken(), isNull);
  });
}
