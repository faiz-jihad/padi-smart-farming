import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/farm/data/models/irrigation_type_model.dart';

void main() {
  group('IrrigationTypeModel', () {
    test('fromJson creates model correctly with string and bool values', () {
      final json = {
        'id': 1,
        'name': 'Irigasi Teknis',
        'code': 'teknis',
        'description': 'Saluran primer, sekunder, dan tersier teratur',
        'is_active': true,
      };

      final model = IrrigationTypeModel.fromJson(json);

      expect(model.id, 1);
      expect(model.name, 'Irigasi Teknis');
      expect(model.code, 'teknis');
      expect(model.description, 'Saluran primer, sekunder, dan tersier teratur');
      expect(model.isActive, isTrue);
    });

    test('fromJson handles string id and integer is_active gracefully', () {
      final json = {
        'id': '5',
        'name': 'Irigasi Pompa Air',
        'code': 'pompa',
        'description': null,
        'is_active': 1,
      };

      final model = IrrigationTypeModel.fromJson(json);

      expect(model.id, 5);
      expect(model.name, 'Irigasi Pompa Air');
      expect(model.code, 'pompa');
      expect(model.description, isNull);
      expect(model.isActive, isTrue);
    });

    test('toJson converts model back to map', () {
      const model = IrrigationTypeModel(
        id: 3,
        name: 'Sawah Tadah Hujan',
        code: 'hujan',
        description: 'Musiman curah hujan',
        isActive: true,
      );

      final json = model.toJson();

      expect(json['id'], 3);
      expect(json['name'], 'Sawah Tadah Hujan');
      expect(json['code'], 'hujan');
      expect(json['description'], 'Musiman curah hujan');
      expect(json['is_active'], isTrue);
    });
  });
}
