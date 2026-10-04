import 'package:flutter_test/flutter_test.dart';
import 'package:garden_app/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const GardenFlutterApp());
    expect(find.byType(GardenFlutterApp), findsOneWidget);
  });
}
